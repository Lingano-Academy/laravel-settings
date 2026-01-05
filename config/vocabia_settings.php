<?php

return [
    'table_name' => 'settings',

    'load_migrations' => env('VOCABIA_SETTINGS_LOAD_MIGRATIONS', true),

    'cache' => [
        'enabled' => true,
        'store'   => 'redis',
        'ttl'     => 3600,
        'prefix'  => 'setting_',
    ],
];