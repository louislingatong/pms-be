<?php

return [
    'statuses' => [
        'due' => 'DUE',
        'warning' => 'WARNING',
        'overdue' => 'OVERDUE',
        'jobs_done' => 'JOBS DONE',
        'dry_dock' => 'DRY DOCK',
    ],
    'warning_days' => env('WARNING_DAYS', 5),
];
