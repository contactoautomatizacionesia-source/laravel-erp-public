<?php

namespace Modules\Incidents\Console;

use Illuminate\Console\Command;
use Modules\Incidents\Jobs\EscalateOverdueStatementsJob;

class EscalateIncidentsCommand extends Command
{
    protected $signature   = 'incidents:escalate';
    protected $description = 'Escala novedades cuyo plazo de pronunciamiento ha vencido.';

    public function handle(): int
    {
        EscalateOverdueStatementsJob::dispatch();
        $this->info('[Incidents] Job de escalado despachado.');
        return Command::SUCCESS;
    }
}
