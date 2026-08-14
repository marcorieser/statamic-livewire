<?php

declare(strict_types=1);

use function Orchestra\Testbench\workbench_path;

return [
    'repository' => 'file',

    'repositories' => [

        'file' => [
            'driver' => 'file',
            'paths' => [
                'roles' => workbench_path('resources/users/roles.yaml'),
                'groups' => workbench_path('resources/users/groups.yaml'),
            ],
        ],

        'eloquent' => [
            'driver' => 'eloquent',
        ],

    ],
];
