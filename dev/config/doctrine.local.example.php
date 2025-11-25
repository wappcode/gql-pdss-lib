<?php

return [
    'driver' => [
        'user'     =>   getenv('PDSSLIV_DBUSER', true) ? getenv('PDSSLIV_DBUSER', true) : 'root',
        'password' =>   getenv('PDSSLIV_DBPASSWORD', true) ? getenv('PDSSLIV_DBPASSWORD', true) : 'dbpassword',
        'dbname'   =>   getenv('PDSSLIV_DBNAME', true) ? getenv('PDSSLIV_DBNAME', true) : 'gqlpdsslib',
        'driver'   =>   getenv('PDSSLIV_DRIVER', true) ? getenv('PDSSLIV_DRIVER', true) : 'pdo_mysql',
        'host'   =>    getenv('PDSSLIV_DBHOST', true) ? getenv('PDSSLIV_DBHOST', true) : 'localhost',
        'charset' =>     'utf8mb4',
        'port' => getenv('PDSSLIV_MYSQL_PORT', true) ? getenv('PDSSLIV_MYSQL_PORT', true) : '3306',
    ],
    'entities' => require __DIR__ . '/doctrine.entities.php',
];
