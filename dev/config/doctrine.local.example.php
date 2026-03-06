<?php

return [
    'driver' => [
        'user'     =>   getenv('PDSSLIB_DBUSER', true) ? getenv('PDSSLIB_DBUSER', true) : 'root',
        'password' =>   getenv('PDSSLIB_DBPASSWORD', true) ? getenv('PDSSLIB_DBPASSWORD', true) : 'dbpassword',
        'dbname'   =>   getenv('PDSSLIB_DBNAME', true) ? getenv('PDSSLIB_DBNAME', true) : 'gqlpdsslib',
        'driver'   =>   getenv('PDSSLIB_DRIVER', true) ? getenv('PDSSLIB_DRIVER', true) : 'pdo_mysql',
        'host'   =>    getenv('PDSSLIB_DBHOST', true) ? getenv('PDSSLIB_DBHOST', true) : 'localhost',
        'charset' =>     'utf8mb4',
        'port' => '3306', // Puerto interno de MySQL en Docker
    ],
    'entities' => require __DIR__ . '/doctrine.entities.php',
];
