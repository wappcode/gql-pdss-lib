<?php

use GPDCore\Factory\EntityManagerFactory;
use GQLBasicClient\GQLClient;

require_once __DIR__ . '/../../vendor/autoload.php';
$options = require __DIR__ . '/../config/doctrine.local.php';
$cacheDir = __DIR__ . '/../data/DoctrineORMModule';
global $entityManager;
global $gqlClient;
$app_port = getenv('PDSSLIB_APP_PORT') ? getenv('PDSSLIB_APP_PORT') : '80';

$entityManager = EntityManagerFactory::createInstance($options, $cacheDir, true, '');
$gqlClient = new GQLClient("http://localhost/index.php/api");
