<?php

declare(strict_types=1);

namespace GPDCore\Infrastructure\Doctrine;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\ORM\ORMSetup;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Exception;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

class EntityManagerFactory
{
    public static function createInstance(array $options, string $cacheDir = '', bool $isDevMode = false, bool $writeLog = false): EntityManager
    {
        $paths = $options['entities']  ?? [];
        $xml = $options['xml'] ?? [];
        $driver = $options['driver'];
        $isDevMode = $isDevMode;
        $cache = null;
        $defaultCacheDir = __DIR__ . '/../../../../../../data/DoctrineORMModule/';
        $attributePaths = array_values($paths);
        $xmlPaths = array_values($xml);
        $xmlDriver = new XmlDriver($xmlPaths);
        $attributeDriver = new AttributeDriver($attributePaths);
        $driverChain = new MappingDriverChain();

        foreach ($xml as $namespace => $path) {
            $driverChain->addDriver($xmlDriver, $namespace);
        }
        foreach ($paths as $namespace => $path) {
            $driverChain->addDriver($attributeDriver, $namespace);
        }

        if (empty($cacheDir)) {
            $cacheDir = $defaultCacheDir;
        }

        if (!$isDevMode && !file_exists($cacheDir)) {
            throw new Exception('The directory ' . $cacheDir . ' does not exist');
        }

        $proxyDir = $cacheDir . '/Proxy';
        $config = ORMSetup::createConfiguration($isDevMode);
        $config->setMetadataDriverImpl($driverChain);


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
    private function __construct() {}

    /**
     * prevent the instance from being cloned (which would create a second instance of it).
     */
    private function __clone() {}
}
