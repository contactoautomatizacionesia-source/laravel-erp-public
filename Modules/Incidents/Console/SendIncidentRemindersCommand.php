<?php

namespace Modules\Incidents\Console;

use Illuminate\Console\Command;
use Modules\Incidents\Jobs\SendStatementReminderJob;

class SendIncidentRemindersCommand extends Command
{
    protected $signature   = 'incidents:send-reminders';
    protected $description = 'Envía recordatorios a los asesores origen antes del vencimiento del plazo.';

    public function handle(): int
    {
        SendStatementReminderJob::dispatch();
        $this->info('[Incidents] Job de recordatorios despachado.');
        return Command::SUCCESS;
    }
}
