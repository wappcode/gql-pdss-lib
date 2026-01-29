<?php

namespace GPDCore\Graphql;

use GPDCore\Contracts\AppContextInterface;


abstract class AbstractCustomTypeFactory
{
    protected static $name;

    protected static $description = '';

    /**
     * @var AppContextInterface
     */
    protected static $context;

    abstract public static function get(?AppContextInterface $context = null, ?string $name = null, ?string $description = null): callable;

    public static function setValues(?AppContextInterface $context = null, ?string $name = null, ?string $description = null)
    {
        if ($context !== null) {
            static::$context = $context;
        }
        if ($name !== null) {
            static::$name = $name;
        }
        if ($description !== null) {
            static::$description = $description;
        }
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
