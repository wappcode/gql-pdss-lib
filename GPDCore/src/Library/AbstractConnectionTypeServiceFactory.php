<?php

namespace GPDCore\Library;

use GraphQL\Type\Definition\ObjectType;
use GPDCore\Graphql\ConnectionTypeFactory;

/**
 * Uso: Sobreescribir esta clase;
 * Sobreescribir constante NAME
 * Agregar variable statica $instance = null
 * 
 */
class AbstractConnectionTypeServiceFactory
{
    const NAME = '---NAME-OVEWRITE---';
    const DESCRIPTION = '';
    protected static $instance = null;

    public static function get(?IContextService $context = null, string $edgeTypeName): ObjectType
    {
        $name = static::NAME;
        $description = static::DESCRIPTION;
        if (static::$instance === null) {
            $edgeType = $context->getServiceManager()->get($edgeTypeName);
            static::$instance = ConnectionTypeFactory::createConnectionType($context, $edgeType, $name, $description);
        }
        return static::$instance;
    }
    public static function getFactory(IContextService $context, string $edgeTypeName): callable
    {
        return function ($sm) use ($context, $edgeTypeName) {
            return static::get($context, $edgeTypeName);
        };
    }
    private function __construct()
    {
    }
    private function __clone()
    {
    }
    private function __wakeup()
    {
    }
}
