<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class LocalizeDatabaseUrlsCommand extends Command
{
    protected $signature = 'db:localize
                            {from : URL de origen a reemplazar (ej: https://amazingsite.igni-soft.com)}
                            {--to= : URL de destino. Por defecto usa APP_URL del .env}
                            {--dry-run : Solo muestra qué cambiaría, sin modificar la DB}
                            {--exclude=* : Tablas adicionales a excluir (además de las predeterminadas)}
                            {--only=* : Si se especifica, solo escanea estas tablas}';

    protected $description = 'Reemplaza una URL hardcodeada en toda la DB. Útil al restaurar un dump de producción en local.';

    private const FILE_APP_INSTALLED = '.app_installed';

    /**
     * Tablas que nunca se deben modificar (logs, historial, auditoría).
     */
    protected array $defaultExcludes = [
        'activity_log',
        'log_activity',
        'failed_jobs',
        'jobs',
        'migrations',
        'telescope_entries',
        'telescope_entries_tags',
        'telescope_monitoring',
        'password_resets',
        'personal_access_tokens',
        'oauth_access_tokens',
        'oauth_refresh_tokens',
    ];

    public function handle(): int
    {
        $from = rtrim($this->argument('from'), '/');
        $to   = rtrim($this->option('to') ?: config('app.url'), '/');
        $host = parse_url($from, PHP_URL_HOST);

        if ($from === $to) {
            $this->error("La URL de origen y destino son iguales: {$from}");
            return self::FAILURE;
        }

        if (! $host) {
            $this->error("No se pudo extraer el host de la URL de origen: {$from}");
            return self::FAILURE;
        }

        $isDryRun = $this->option('dry-run');

        $this->printHeader($from, $to, $host, $isDryRun);

        $results = $this->scanTables($from, $to, $host, $isDryRun);

        $this->printReport($results, $host, $isDryRun);

        if (! $isDryRun) {
            $this->ensureRuntimeFiles();
        }

        return self::SUCCESS;
    }

    /**
     * Imprime el encabezado informativo antes del escaneo.
     */
    protected function printHeader(string $from, string $to, string $host, bool $isDryRun): void
    {
        $this->info('');
        $this->line("  <fg=cyan>Origen:</>  {$from}");
        $this->line("  <fg=cyan>Destino:</> {$to}");
        $this->line("  <fg=cyan>Buscando host:</> {$host}");
        if ($isDryRun) {
            $this->warn('  [DRY-RUN] No se modificará ningún dato.');
        }
        $this->info('');
    }

    /**
     * Recorre todas las tablas elegibles y procesa cada columna de texto.
     * Devuelve el listado de columnas afectadas con su conteo de filas.
     */
    protected function scanTables(string $from, string $to, string $host, bool $isDryRun): array
    {
        $tables = $this->resolveTables();

        $results = [];
        $bar     = $this->output->createProgressBar(count($tables));
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% — %message%');
        $bar->start();

        foreach ($tables as $table) {
            $bar->setMessage($table);
            $bar->advance();

            foreach ($this->getColumnsForTable($table) as ['name' => $column, 'json' => $isJson]) {
                $count = DB::table($table)->where($column, 'like', "%{$host}%")->count();

                if ($count === 0) {
                    continue;
                }

                if (! $isDryRun) {
                    $this->replaceInColumn($table, $column, $from, $to, $host, $isJson);
                }

                $results[] = [
                    'table'  => $table,
                    'column' => $column,
                    'rows'   => $count,
                    'status' => $isDryRun ? '<fg=yellow>pendiente</>' : '<fg=green>actualizado</>',
                ];
            }
        }

        $bar->setMessage('completado');
        $bar->finish();
        $this->info('');
        $this->info('');

        return $results;
    }

    /**
     * Devuelve la lista de tablas a escanear, aplicando filtros --only y --exclude.
     */
    protected function resolveTables(): array
    {
        $tables   = $this->getAllTables();
        $only     = $this->option('only');
        $excludes = array_merge($this->defaultExcludes, $this->option('exclude'));

        if (! empty($only)) {
            $tables = array_intersect($tables, $only);
        }

        return array_values(array_diff($tables, $excludes));
    }

    /**
     * Devuelve las columnas de texto de una tabla listas para iterar,
     * con su flag 'json' para elegir la estrategia de reemplazo.
     *
     * @return array<int, array{name: string, json: bool}>
     */
    protected function getColumnsForTable(string $table): array
    {
        $cols = $this->getTextColumns($table);

        return array_merge(
            array_map(fn ($c) => ['name' => $c, 'json' => false], $cols['plain']),
            array_map(fn ($c) => ['name' => $c, 'json' => true],  $cols['json']),
        );
    }

    /**
     * Imprime la tabla de resultados y el resumen final.
     */
    protected function printReport(array $results, string $host, bool $isDryRun): void
    {
        if (empty($results)) {
            $this->info("No se encontraron ocurrencias de \"{$host}\" en la base de datos.");
            return;
        }

        $this->table(
            ['Tabla', 'Columna', 'Filas', 'Estado'],
            array_map(fn ($r) => [$r['table'], $r['column'], $r['rows'], $r['status']], $results)
        );

        $totalRows = array_sum(array_column($results, 'rows'));
        $totalCols = count($results);
        $this->info('');

        if ($isDryRun) {
            $this->warn("  [DRY-RUN] Se encontraron {$totalRows} fila(s) en {$totalCols} columna(s). Ejecuta sin --dry-run para aplicar.");
        } else {
            $this->info("  Reemplazo completado: {$totalRows} fila(s) actualizadas en {$totalCols} columna(s).");
        }

        $this->info('');
    }

    /**
     * Devuelve todas las tablas de la base de datos actual.
     */
    protected function getAllTables(): array
    {
        $database = config('database.connections.' . config('database.default') . '.database');
        $rows     = DB::select('SHOW TABLES');
        $key      = "Tables_in_{$database}";

        return array_map(fn ($row) => $row->$key, $rows);
    }

    /**
     * Devuelve las columnas de tipo texto/json de una tabla,
     * separadas por si son JSON nativo o texto plano.
     *
     * @return array{plain: string[], json: string[]}
     */
    protected function getTextColumns(string $table): array
    {
        try {
            $columns = DB::select('SHOW COLUMNS FROM `' . $table . '`');
        } catch (\Throwable) {
            return ['plain' => [], 'json' => []];
        }

        $plain = [];
        $json  = [];

        foreach ($columns as $col) {
            if (preg_match('/json/i', $col->Type)) {
                $json[] = $col->Field;
            } elseif (preg_match('/char|text|blob/i', $col->Type)) {
                $plain[] = $col->Field;
            }
        }

        return ['plain' => $plain, 'json' => $json];
    }

    /**
     * Reemplaza la URL en una columna.
     *
     * - Columnas de texto plano: UPDATE masivo con REPLACE() de SQL — una sola query.
     * - Columnas JSON nativas: procesamiento fila a fila para no romper la estructura,
     *   usando la PK real de la tabla (si existe); se omite la tabla si no tiene PK simple.
     */
    protected function replaceInColumn(string $table, string $column, string $from, string $to, string $host, bool $isJson): void
    {
        if (! $isJson) {
            // Un solo UPDATE para toda la tabla — no requiere PK
            DB::table($table)
                ->where($column, 'like', "%{$host}%")
                ->update([$column => DB::raw("REPLACE(`{$column}`, " . DB::getPdo()->quote($from) . ', ' . DB::getPdo()->quote($to) . ')')]);
            return;
        }

        // JSON: necesitamos la PK para actualizar fila a fila de forma segura
        $primaryKey = $this->getPrimaryKey($table);
        if ($primaryKey === null) {
            // Sin PK simple no podemos actualizar con seguridad — omitir
            return;
        }

        $rows = DB::table($table)
            ->where($column, 'like', "%{$host}%")
            ->get([$primaryKey, $column]);

        foreach ($rows as $row) {
            $original = $row->$column;
            $replaced = $this->replaceJsonValue($original, $from, $to);

            if ($replaced !== $original) {
                DB::table($table)->where($primaryKey, $row->$primaryKey)->update([$column => $replaced]);
            }
        }
    }

    /**
     * Devuelve el nombre de la columna PK simple de una tabla, o null si no tiene.
     */
    protected function getPrimaryKey(string $table): ?string
    {
        $columns = DB::select('SHOW KEYS FROM `' . $table . '` WHERE Key_name = "PRIMARY"');

        // Solo PK simple (una columna); PKs compuestas devuelven múltiples filas
        if (count($columns) === 1) {
            return $columns[0]->Column_name;
        }

        return null;
    }

    /**
     * Reemplaza la URL dentro de un valor JSON serializado sin romper la estructura.
     * Decodifica el JSON y recorre recursivamente solo los valores string,
     * dejando claves e integridad estructural intactos.
     */
    protected function replaceJsonValue(string $value, string $from, string $to): string
    {
        $decoded = json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            return str_replace($from, $to, $value);
        }

        return json_encode(
            $this->replaceRecursive($decoded, $from, $to),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Recorre un array recursivamente reemplazando la URL solo en valores string.
     * Las claves y los tipos no-string (int, bool, null) no se tocan.
     */
    protected function replaceRecursive(array $data, string $from, string $to): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->replaceRecursive($value, $from, $to);
            } elseif (is_string($value)) {
                $data[$key] = str_replace($from, $to, $value);
            }
        }

        return $data;
    }

    /**
     * Verifica y crea los archivos de runtime que el sistema necesita para arrancar.
     * Estos archivos no viajan con el repositorio ni con el dump de DB, por lo que
     * cualquier entorno que parta de un dump limpio los necesita recrear.
     */
    protected function ensureRuntimeFiles(): void
    {
        $this->info('  Verificando archivos de runtime...');
        $created = [];

        // .app_installed — el middleware AmazCartService redirige a /install si no existe.
        // Su contenido debe ser el checksum registrado en infix_module_managers.
        if (! Storage::exists(self::FILE_APP_INSTALLED) || ! Storage::get(self::FILE_APP_INSTALLED)) {
            $checksum = DB::table('infix_module_managers')->value('checksum');
            if ($checksum) {
                Storage::put(self::FILE_APP_INSTALLED, $checksum);
                $created[] = 'storage/app/' . self::FILE_APP_INSTALLED;
            }
        }

        // amazy_img.json — usado por themeDefaultImg() para la imagen lazy del tema amazy.
        if (! Storage::exists('amazy_img.json')) {
            Storage::put('amazy_img.json', json_encode(
                ['amazy' => 'frontend/amazy/img/default_img.jpg'],
                JSON_PRETTY_PRINT
            ));
            $created[] = 'storage/app/amazy_img.json';
        }

        // default_img.json — usado por themeDefaultImg() para temas distintos a amazy.
        if (! Storage::exists('default_img.json')) {
            Storage::put('default_img.json', json_encode(
                ['default' => 'frontend/amazy/img/default_img.jpg'],
                JSON_PRETTY_PRINT
            ));
            $created[] = 'storage/app/default_img.json';
        }

        if (empty($created)) {
            $this->line('  <fg=green>✓</> Todos los archivos de runtime ya existen.');
        } else {
            foreach ($created as $file) {
                $this->line("  <fg=yellow>creado</> {$file}");
            }
        }

        $this->info('');
    }
}
