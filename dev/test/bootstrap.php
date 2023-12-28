<?php

use GQLBasicClient\GQLClient;
use GPDCore\Factory\EntityManagerFactory;

require_once __DIR__ . "/../../vendor/autoload.php";
$options = require __DIR__ . "/../config/doctrine.local.php";
$cacheDir = __DIR__ . "/../data/DoctrineORMModule";
global $entityManager;
global $gqlClient;
$app_port = getenv("PDSSLIB_APP_PORT") ? getenv("PDSSLIB_APP_PORT") : "8000";

$entityManager = EntityManagerFactory::createInstance($options, $cacheDir, true, '');
$gqlClient = new GQLClient("http://localhost:{$app_port}/index.php/api");
