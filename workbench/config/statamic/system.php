<?php

declare(strict_types=1);

use function Orchestra\Testbench\workbench_path;

return [
    'multisite' => true,
    'layout' => 'layout',
    'blueprints_path' => workbench_path('resources/blueprints'),
    'fieldsets_path' => workbench_path('resources/fieldsets'),
];
