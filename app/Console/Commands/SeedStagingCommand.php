<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\DiscoversSeeders;
use App\Seeders\Contracts\DeployableSeeder;
use App\Seeders\Contracts\StagingSeeder;
use Illuminate\Console\Command;

class SeedStagingCommand extends Command
{
    use DiscoversSeeders;

    protected $signature   = 'seed:staging {--force : Forzar ejecución en producción sin confirmación} {--dry-run : Lista los seeders que se ejecutarían sin correrlos}';
    protected $description = 'Ejecuta DeployableSeeder + StagingSeeder (develop y local). Idempotente.';

    public function handle(): int
    {
        if (app()->isProduction() && ! $this->option('force') && ! $this->confirm('¿Ejecutar seeders en PRODUCCIÓN?')) {
            return self::SUCCESS;
        }

        $deployable = $this->discoverSeeders(DeployableSeeder::class);
        $staging    = $this->discoverSeeders(StagingSeeder::class);

        // Merge sin duplicados (un seeder no debería implementar ambas, pero por seguridad)
        $seeders = $deployable->merge($staging)->unique();

        if ($seeders->isEmpty()) {
            $this->warn('No se encontraron seeders con DeployableSeeder ni StagingSeeder.');
            return self::SUCCESS;
        }

        $this->info("Seeders encontrados: {$seeders->count()} ({$deployable->count()} deploy + {$staging->count()} staging)");

        if ($this->option('dry-run')) {
            $this->line('<fg=green>-- DeployableSeeder --</>');
            $deployable->each(fn($class) => $this->line("  • {$class}"));
            $this->line('<fg=yellow>-- StagingSeeder --</>');
            $staging->each(fn($class) => $this->line("  • {$class}"));
            return self::SUCCESS;
        }

        foreach ($seeders as $class) {
            $this->line("  → {$class}");
            $this->laravel->make($class)->setContainer($this->laravel)->setCommand($this)->run();
        }

        $this->info('seed:staging completado.');
        return self::SUCCESS;
    }
}
