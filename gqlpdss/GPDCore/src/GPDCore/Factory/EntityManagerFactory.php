<?php

declare(strict_types = 1);

namespace GPDCore\Factory;

use GPDCore\Library\DoctrineSQLLogger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;

// use Doctrine\ORM\Tools\Setup;
// use Doctrine\ORM\EntityManager;

class EntityManagerFactory
{

    private static  $instance = null;

    public static function getInstance(): EntityManager
    {

        if (static::$instance === null) {
            static::$instance = self::createInstance();
        }

        return static::$instance;
       
    }

    private static function createInstance(): EntityManager {
        $env = getenv('APP_ENV');
        $options = require __DIR__.'/../../../../../config/doctrine.local.php';
        $paths = $options["entities"];
        $isDevMode = true;
        $proxyDir = null;
        $cache = null;
        $useSimpleAnnotationReader = false;
        $dbParams = $options["driver"];
        $config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode, $proxyDir, $cache, $useSimpleAnnotationReader);
        // if($env !== 'production') {
        //     $logger = new DoctrineSQLLogger();
        //     $config->setSQLLogger($logger);
        // }
        $entityManager = EntityManager::create($dbParams, $config);
        return $entityManager;
    }

    /**
     * is not allowed to call from outside to prevent from creating multiple instances,
     * to use the singleton, you have to obtain the instance from Singleton::getInstance() instead
     */
    private function __construct()
    {
    }

    /**
     * prevent the instance from being cloned (which would create a second instance of it)
     */
    private function __clone()
    {
    }

    /**
     * prevent from being unserialized (which would create a second instance of it)
     */
    private function __wakeup()
    {
    }
}


