<?php

namespace GPDCore\Services;

class ConfigService
{
    private static $instance;

    private $config;

    public static function getInstance(): ConfigService
    {
        if (static::$instance === null) {
            static::$instance = self::createInstance();
        }

        return static::$instance;
    }

    public function get($value, $default = null)
    {
        return $this->getValue($value, $default);
    }

    public function getValue($value, $default = null)
    {
        return $this->config[$value] ?? $default;
    }

    /**
     * Agrega valores al servicio, si el valor ya se habia fijado lo sobreescribe.
     */
    public function add(array $newConfig): ConfigService
    {
        $instance = static::getInstance();
        $this->config = array_merge($this->config, $newConfig);

        return $instance;
    }

    private static function createInstance(): ConfigService
    {
        $config = [];

        return new ConfigService($config);
    }

    /**
     * is not allowed to call from outside to prevent from creating multiple instances,
     * to use the singleton, you have to obtain the instance from Singleton::getInstance() instead.
     */
    private function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * prevent the instance from being cloned (which would create a second instance of it).
     */
    private function __clone()
    {
    }
}
