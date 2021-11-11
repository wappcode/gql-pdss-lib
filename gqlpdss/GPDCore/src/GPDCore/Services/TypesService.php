<?php

namespace GPDCore\Services;

use Doctrine\ORM\EntityManager;
use Exception;
use GraphQL\Doctrine\Types;
use Laminas\ServiceManager\ServiceManager;

class TypesService {


    private static  $instance = null;
    private static $inited = false;


    public static function init(EntityManager $entityManager, ServiceManager $serviceManager): void {
        if (static::$inited) {
            return;
        }
        static::$instance = new Types($entityManager, $serviceManager);
        static::$inited = true;
    }
    public static function getInstance(): Types
    {
        if (!static::$instance) {
            throw new Exception('Aún no se ha iniciado el servicio');
        }
        return static::$instance;
       
    }
    /**
     * is not allowed to call from outside to prevent from creating multiple instances,
     * to use the singleton, you have to obtain the instance from Singleton::getInstance() instead
     */
    private function __construct(array $config) 
    {
        $this->config = $config;
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