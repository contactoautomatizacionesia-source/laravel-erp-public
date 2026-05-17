<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Livewire\Livewire;

trait LivewireModuleLoader
{
    /**
     * Registra automáticamente los componentes Livewire basándose en la convención del módulo.
     * No requiere parámetros si se siguen los estándares.
     */
    protected function registerLivewireComponents()
    {
        $moduleName = $this->moduleName ?? null;
        $moduleLower = $this->moduleNameLower ?? strtolower($moduleName);

        if (!$moduleName) {
            return;
        }

        $directory = module_path($moduleName, 'Http/Livewire');
        $namespace = "Modules\\{$moduleName}\\Http\\Livewire\\";
        $prefix = "{$moduleLower}::";

        if (is_dir($directory)) {
            foreach (glob($directory . '/*.php') as $file) {
                $className = basename($file, '.php');
                $class = $namespace . $className;
                $alias = Str::kebab($className);
                Livewire::component($prefix . $alias, $class);
            }
        }
    }
}