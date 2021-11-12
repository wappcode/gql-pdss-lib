<?php

declare(strict_types = 1);

namespace GPDCore\Factory;

use GPDCore\Library\DoctrineSQLLogger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;

class EntityManagerFactory
{
    public static function createInstance(array $options, string $proxyDir, bool $production = false, bool $writeLog = false): EntityManager {
        $paths = $options["entities"];
        $isDevMode = true;
        $cache = null;
        $useSimpleAnnotationReader = false;
        $dbParams = $options["driver"];
        $config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode, $proxyDir, $cache, $useSimpleAnnotationReader);
        if ($production) {
            $config->setAutoGenerateProxyClasses(false);
        }
        if(!$production && $writeLog) {
            $logger = new DoctrineSQLLogger();
            $config->setSQLLogger($logger);
        }
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


