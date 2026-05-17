<?php

return [
    'name'          => 'CycleClosure',

    // Default execution day for monthly closures (1-31).
    // Overridden at runtime by the active cycle_settings record.
    'execution_day' => 1,

    // Cron command preview shown in the settings view.
    // Override per environment (.env) to match the server's deploy directory / PHP binary.
    'cron_workdir'  => env('CYCLECLOSURE_CRON_WORKDIR', env('CYCLE_CLOSURE_SERVER_PATH', base_path())),
    'cron_php'      => env('CYCLECLOSURE_CRON_PHP', 'php'),
    'cron_output'   => env('CYCLECLOSURE_CRON_OUTPUT', PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null'),

    // Backwards-compat alias (legacy key used by older views).
    'server_path'   => env('CYCLE_CLOSURE_SERVER_PATH', env('CYCLECLOSURE_CRON_WORKDIR', base_path())),
];
