<?php

namespace App\Console\Commands\Concerns;

use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;

trait DiscoversSeeders
{
    /**
     * Descubre todos los seeders en Modules y database/seeders que implementen la interface dada.
     */
    protected function discoverSeeders(string $interface): \Illuminate\Support\Collection
    {
        $modulePaths = File::isDirectory(base_path('Modules'))
            ? collect(File::directories(base_path('Modules')))
                ->map(fn(string $path) => $path . '/Database/Seeders')
                ->all()
            : [];

        return collect(array_merge($modulePaths, [database_path('seeders')]))
            ->filter(fn(string $path) => File::isDirectory($path))
            ->flatMap(fn(string $path) => File::allFiles($path))
            ->filter(fn(SplFileInfo $f) => $f->getExtension() === 'php')
            ->map(fn(SplFileInfo $f) => $this->resolveClass($f->getRealPath()))
            ->filter()
            ->filter(fn(string $class) => $this->classImplements($class, $interface))
            ->values();
    }

    /**
     * Extrae el FQCN de un archivo PHP usando el tokenizador nativo.
     * Maneja clases abstractas, finales, atributos PHP 8 y comentarios.
     */
    protected function resolveClass(string $filePath): ?string
    {
        $tokens    = token_get_all(File::get($filePath));
        $namespace = $this->extractNamespace($tokens);
        $class     = $this->extractClassName($tokens);

        if ($namespace === '' || $class === '') {
            return null;
        }

        $fqcn = $namespace . '\\' . $class;

        if (! class_exists($fqcn)) {
            require_once $filePath;
        }

        return class_exists($fqcn) ? $fqcn : null;
    }

    private function extractNamespace(array $tokens): string
    {
        $count = count($tokens);
        $i     = 0;

        while ($i < $count) {
            if (! is_array($tokens[$i]) || $tokens[$i][0] !== T_NAMESPACE) {
                $i++;
                continue;
            }

            $ns = '';
            $i++;
            while ($i < $count) {
                $t = $tokens[$i];
                if (is_array($t) && in_array($t[0], [T_STRING, T_NS_SEPARATOR, T_NAME_QUALIFIED], true)) {
                    $ns .= $t[1];
                } elseif ($t === ';' || $t === '{') {
                    break;
                }
                $i++;
            }

            return $ns;
        }

        return '';
    }

    private function extractClassName(array $tokens): string
    {
        $count = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            if (! is_array($tokens[$i]) || $tokens[$i][0] !== T_CLASS) {
                continue;
            }

            for ($j = $i + 1; $j < $count; $j++) {
                $t = $tokens[$j];
                if (is_array($t) && $t[0] === T_WHITESPACE) {
                    continue;
                }
                return (is_array($t) && $t[0] === T_STRING) ? $t[1] : '';
            }
        }

        return '';
    }

    protected function classImplements(string $class, string $interface): bool
    {
        try {
            return in_array($interface, class_implements($class) ?: [], true);
        } catch (\Throwable) {
            return false;
        }
    }
}
