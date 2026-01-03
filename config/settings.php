<?php

return [
    'table_name' => 'settings',

    'cache' => [
        'enabled' => true,
        'store'   => 'redis',
        'ttl'     => 3600,
        'prefix'  => 'setting_',
    ],
];