<?php

declare(strict_types = 1);

namespace GPDCore\Factory;

use GPDCore\Library\DoctrineSQLLogger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;

class EntityManagerFactory
{
    public static function createInstance(array $options, string $proxyDir, bool $isDevMode = false, bool $writeLog = false): EntityManager {

        $paths = $options["entities"];
        $driver = $options["driver"];
        $isDevMode = $isDevMode;
        $useSimpleAnnotationReader = false;
        $cache = null;
        if ($isDevMode) {
            $proxyDir = null;
        }
        $config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode, $proxyDir, $cache, $useSimpleAnnotationReader);
        if($isDevMode && $writeLog) {
            $logger = new DoctrineSQLLogger();
            $config->setSQLLogger($logger);
        }
        $entityManager = EntityManager::create($driver, $config);
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


