<?php

return [
    'table_name' => env('VOCABIA_SETTINGS_TABLE', 'vocabia_settings'),

    'load_migrations' => env('VOCABIA_SETTINGS_LOAD_MIGRATIONS', false),

    'cache' => [
        'enabled' => true,
        'store'   => 'redis',
        'ttl'     => 3600,
        'prefix'  => 'setting_',
    ],
];