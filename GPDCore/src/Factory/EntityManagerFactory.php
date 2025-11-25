<?php

declare(strict_types=1);

namespace GPDCore\Factory;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Exception;
use GPDCore\Library\DoctrineSQLLogger;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

class EntityManagerFactory
{
    public static function createInstance(array $options, string $cacheDir = '', bool $isDevMode = false, bool $writeLog = false): EntityManager
    {
        $paths = $options['entities'];
        $driver = $options['driver'];
        $isDevMode = $isDevMode;
        $cache = null;
        $defaultCacheDir = __DIR__ . '/../../../../../../data/DoctrineORMModule/';

        if (empty($cacheDir)) {
            $cacheDir = $defaultCacheDir;
        }

        if (!$isDevMode && !file_exists($cacheDir)) {
            throw new Exception('The directory ' . $cacheDir . ' does not exist');
        }

        $proxyDir = $cacheDir . '/Proxy';
        $config = ORMSetup::createAttributeMetadataConfiguration($paths, $isDevMode, $proxyDir, $cache);

        // TODO: buscar nueva forma para guardar el log
        // if ($isDevMode && $writeLog) {
        //     $logger = new DoctrineSQLLogger();
        //     $config->setSQLLogger($logger);
        // }
        if (!$isDevMode && !empty($cacheDir)) {
            $cacheQueryDir = $cacheDir . '/Query';
            $cacheMetadataDir = $cacheDir . '/Metadata';
            $cacheQueryDriver = new PhpFilesAdapter('doctrine_query_cache', 0, $cacheQueryDir, true);
            $cacheMetadataDriver = new PhpFilesAdapter('doctrine_metadata_cache', 0, $cacheMetadataDir, true);
            $config->setQueryCache($cacheQueryDriver);
            $config->setMetadataCache($cacheMetadataDriver);
        }
        $connection = DriverManager::getConnection($driver, $config);
        $entityManager = new EntityManager($connection, $config);

        return $entityManager;
    }

    /**
     * is not allowed to call from outside to prevent from creating multiple instances,
     * to use the singleton, you have to obtain the instance from Singleton::getInstance() instead.
     */
    private function __construct()
    {
    }

    /**
     * prevent the instance from being cloned (which would create a second instance of it).
     */
    private function __clone()
    {
    }
}
