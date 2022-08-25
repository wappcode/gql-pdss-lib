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
class AbstractEdgeTypeServiceFactory
{


    const NAME = '---NAME-OVEWRITE---';
    const DESCRIPTION = '';
    protected static $instance = null;

    public static function get(?IContextService $context = null, ObjectType $nodeType): ObjectType
    {
        $name = static::NAME;
        $description = static::DESCRIPTION;
        if (static::$instance === null) {
            static::$instance = ConnectionTypeFactory::createEdgeType($nodeType, $name, $description);
        }
        return static::$instance;
    }
    public static function getFactory(IContextService $context, ObjectType $nodeType): callable
    {
        return function ($sm) use ($context, $nodeType) {
            return static::get($context, $nodeType);
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
