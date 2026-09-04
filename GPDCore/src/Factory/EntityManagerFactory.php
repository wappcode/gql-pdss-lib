<?php

declare(strict_types=1);

namespace GPDCore\Factory;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\ORM\ORMSetup;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Exception;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

class EntityManagerFactory
{
    /**
     * Alias a method to create an instance of EntityManager using attributes.
     *
     * @param array $options
     * @param string $cacheDir
     * @param boolean $isDevMode
     * @param boolean $writeLog
     * @return EntityManager
     */
    public static function createInstance(array $options, string $cacheDir = '', bool $isDevMode = false, bool $writeLog = false): EntityManager
    {
        return static::createAttributesInstance($options, $cacheDir, $isDevMode, $writeLog);
    }

    public static function createAttributesInstance(array $options, string $cacheDir = '', bool $isDevMode = false, bool $writeLog = false): EntityManager
    {
        $paths = $options['entities'];
        $driver = $options['driver'];
        $cache = null; // Se define posteriormente al cerar la instancia base
        $proxyDir = null; // Se define posteriormente al cerar la instancia base
        $config = ORMSetup::createAttributeMetadataConfiguration($paths, $isDevMode, $proxyDir, $cache);
        $entityManager = static::createBaseInstance($config, $driver, $cacheDir, $isDevMode, $writeLog);

        return $entityManager;
    }

    public static function createXMLInstance(array $options, string $cacheDir = '', bool $isDevMode = false, bool $isXsdValidationEnabled = true, bool $writeLog = false): EntityManager
    {
        $paths = $options['xml'];
        $driver = $options['driver'];
        $cache = null; // Se define posteriormente al cerar la instancia base
        $proxyDir = null; // Se define posteriormente al cerar la instancia base
        $config = ORMSetup::createXMLMetadataConfiguration($paths, $isDevMode, $proxyDir, $cache, $isXsdValidationEnabled);
        $entityManager = static::createBaseInstance($config, $driver, $cacheDir, $isDevMode, $writeLog);

        return $entityManager;
    }

    public static function createAttributesAndXmlInstance(array $options, string $cacheDir = '', bool $isDevMode = false, bool $isXsdValidationEnabled = true, bool $writeLog = false): EntityManager
    {
        $entities = $options['entities'];
        $driver = $options['driver'];
        $xml = $options['xml'];
        $cache = null; // Se define posteriormente al cerar la instancia base
        $proxyDir = null; // Se define posteriormente al cerar la instancia base
        $proxyDir = $cacheDir . '/Proxy';
        $attributePaths = array_values($entities);
        $xmlPaths = array_values($xml);
        $xmlDriver = new XmlDriver($xmlPaths);
        $attributeDriver = new AttributeDriver($attributePaths);
        $driverChain = new MappingDriverChain();

        foreach ($xml as $namespace => $path) {
            $driverChain->addDriver($xmlDriver, $namespace);
        }
        foreach ($entities as $namespace => $path) {
            $driverChain->addDriver($attributeDriver, $namespace);
        }

        $config = ORMSetup::createConfiguration($isDevMode, $proxyDir, $cache);
        $config->setMetadataDriverImpl($driverChain);
        $entityManager = static::createBaseInstance($config, $driver, $cacheDir, $isDevMode, $writeLog);

        return $entityManager;
    }

    private static function createBaseInstance(Configuration $config, array $driver, string $cacheDir = '', bool $isDevMode = false, bool $writeLog = false)
    {
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
        $config->setProxyDir($proxyDir);
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
