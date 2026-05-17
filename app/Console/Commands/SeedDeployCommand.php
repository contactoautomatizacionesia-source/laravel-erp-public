<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\DiscoversSeeders;
use App\Seeders\Contracts\DeployableSeeder;
use Illuminate\Console\Command;

class SeedDeployCommand extends Command
{
    use DiscoversSeeders;

    protected $signature   = 'seed:deploy {--force : Forzar ejecución en producción sin confirmación} {--dry-run : Lista los seeders que se ejecutarían sin correrlos}';
    protected $description = 'Ejecuta todos los seeders DeployableSeeder (producción + develop). Idempotente.';

    public function handle(): int
    {
        if (app()->isProduction() && ! $this->option('force') && ! $this->confirm('¿Ejecutar seeders en PRODUCCIÓN?')) {
            return self::SUCCESS;
        }

        $seeders = $this->discoverSeeders(DeployableSeeder::class);

        if ($seeders->isEmpty()) {
            $this->warn('No se encontraron seeders con DeployableSeeder.');
            return self::SUCCESS;
        }

        $this->info("Seeders encontrados: {$seeders->count()}");

        if ($this->option('dry-run')) {
            $seeders->each(fn($class) => $this->line("  • {$class}"));
            return self::SUCCESS;
        }

        foreach ($seeders as $class) {
            $this->line("  → {$class}");
            $this->laravel->make($class)->setContainer($this->laravel)->setCommand($this)->run();
        }

        $this->info('seed:deploy completado.');
        return self::SUCCESS;
    }
}
