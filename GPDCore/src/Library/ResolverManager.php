<?php

declare(strict_types=1);

namespace GPDCore\Library;

/**
 * Registra todos los resolvers de Doctrine Entities para ser usados por el resolver predeterminado del servidor.
 */
class ResolverManager
{
    private static $resolvers = [];

    public static function add(string $key, callable $resolver)
    {
        self::$resolvers[$key] = $resolver;
    }

    public static function get(string $key)
    {
        return self::$resolvers[$key] ?? null;
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
