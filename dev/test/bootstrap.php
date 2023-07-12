<?php

use GPDCore\Factory\EntityManagerFactory;

require_once __DIR__ . "/../../vendor/autoload.php";
$options = require __DIR__ . "/../config/doctrine.local.php";
$cacheDir = __DIR__ . "/../data/DoctrineORMModule";
$entityManager = EntityManagerFactory::createInstance($options, $cacheDir, true, '');
