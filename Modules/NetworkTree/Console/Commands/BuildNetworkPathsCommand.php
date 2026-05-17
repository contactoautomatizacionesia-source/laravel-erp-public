<?php

namespace Modules\NetworkTree\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\NetworkTree\Services\NetworkTreeManager;

class BuildNetworkPathsCommand extends Command
{
    protected $signature = 'network:build-paths
                            {--fresh : Limpia la tabla antes de reconstruir (útil en local)}
                            {--chunk=100 : Tamaño del chunk para procesar empresarios}';

    protected $description = 'Construye (o reconstruye) la closure table network_paths a partir de representative_id en customer_profiles';

    private int $processed = 0;
    private int $skipped   = 0;

    public function __construct(private NetworkTreeManager $treeManager)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if (! $this->confirmFresh()) {
            return self::SUCCESS;
        }

        $chunkSize = (int) $this->option('chunk');
        $total     = DB::table('customer_profiles')->whereNotNull('user_id')->count();

        $this->info("Procesando {$total} empresarios en chunks de {$chunkSize}...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $this->insertOrphanParents();
        $this->insertRoots($chunkSize, $bar);
        $this->insertChildrenIteratively($chunkSize, $bar);

        $bar->finish();
        $this->newLine(2);
        $this->info("✓ Completado: {$this->processed} nodos insertados, {$this->skipped} omitidos (ya existían o sin user).");

        return self::SUCCESS;
    }

    // -------------------------------------------------------------------------
    // Pasos del proceso
    // -------------------------------------------------------------------------

    /**
     * Inserta como raíces los users referenciados como representative_id
     * que no tienen customer_profile propio (ej. admins que patrocinaron a alguien).
     */
    private function insertOrphanParents(): void
    {
        $orphanParents = DB::table('customer_profiles as cp')
            ->whereNotNull('cp.representative_id')
            ->whereNotExists(fn($q) => $q->from('customer_profiles as cp2')->whereColumn('cp2.user_id', 'cp.representative_id'))
            ->whereExists(fn($q) => $q->from('users')->whereColumn('users.id', 'cp.representative_id'))
            ->pluck('cp.representative_id')
            ->unique();

        foreach ($orphanParents as $parentUserId) {
            if (! $this->isInTree((int) $parentUserId)) {
                $this->treeManager->insertNode((int) $parentUserId, null);
                $this->line("  Padre sin perfil insertado como raíz: user_id={$parentUserId}");
            }
        }
    }

    /**
     * Inserta los empresarios sin representative_id (raíces del árbol).
     */
    private function insertRoots(int $chunkSize, $bar): void
    {
        DB::table('customer_profiles')
            ->whereNull('representative_id')
            ->whereNotNull('user_id')
            ->orderBy('user_id')
            ->chunk($chunkSize, function ($profiles) use ($bar) {
                foreach ($profiles as $profile) {
                    $this->processRoot($profile, $bar);
                }
            });
    }

    private function processRoot(object $profile, $bar): void
    {
        if (! $this->userExists((int) $profile->user_id)) {
            $this->skipped++;
            $bar->advance();
            return;
        }

        if ($this->isInTree((int) $profile->user_id)) {
            $this->skipped++;
        } else {
            $this->treeManager->insertNode($profile->user_id, null);
            $this->processed++;
        }

        $bar->advance();
    }

    /**
     * Inserta los nodos con padre usando BFS (breadth-first search).
     *
     * En cada nivel solo se consultan los hijos directos de los nodos
     * procesados en el nivel anterior, evitando escanear toda la tabla
     * repetidamente.
     */
    private function insertChildrenIteratively(int $chunkSize, $bar): void // NOSONAR
    {
        // Semilla: IDs de todos los nodos ya en el árbol (raíces + orphan parents)
        $currentLevelIds = DB::table('network_paths')
            ->where('depth', 0)
            ->pluck('entrepreneur_id')
            ->all();

        while (! empty($currentLevelIds)) {
            $nextLevelIds = [];

            // IDs ya presentes en el árbol al inicio de este nivel (lookup O(1) en PHP)
            $alreadyInTree = DB::table('network_paths')
                ->where('depth', 0)
                ->pluck('entrepreneur_id')
                ->flip()
                ->all();

            // Solo consultamos hijos directos de los nodos del nivel actual
            DB::table('customer_profiles')
                ->whereIn('representative_id', $currentLevelIds)
                ->whereNotNull('user_id')
                ->orderBy('user_id')
                ->chunk($chunkSize, function ($profiles) use ($bar, &$nextLevelIds, $alreadyInTree) {
                    foreach ($profiles as $profile) {
                        $userId = (int) $profile->user_id;

                        if (isset($alreadyInTree[$userId])) {
                            $bar->advance();
                            continue;
                        }

                        if (! $this->userExists($userId)) {
                            $this->skipped++;
                            $bar->advance();
                            continue;
                        }

                        $this->treeManager->insertNode($userId, (int) $profile->representative_id);
                        $this->processed++;
                        $bar->advance();

                        $nextLevelIds[] = $userId;
                    }
                });

            $currentLevelIds = $nextLevelIds;
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function confirmFresh(): bool
    {
        if (! $this->option('fresh')) {
            return true;
        }

        if (! $this->confirm('¿Seguro que deseas limpiar network_paths y reconstruir desde cero?')) {
            $this->info('Cancelado.');
            return false;
        }

        DB::table('network_paths')->truncate();
        $this->info('Tabla network_paths limpiada.');
        return true;
    }

    private function isInTree(int $userId): bool
    {
        return DB::table('network_paths')
            ->where('entrepreneur_id', $userId)
            ->where('depth', 0)
            ->exists();
    }

    private function userExists(int $userId): bool
    {
        return DB::table('users')->where('id', $userId)->exists();
    }
}
