<?php

namespace GPDCore\Library;

use GraphQL\Type\Definition\ObjectType;
use GPDCore\Graphql\ConnectionTypeFactory;
use GPDCore\Library\UndefinedTypesException;

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

    public static function get(?IContextService $context = null, string $nodeClassName): ObjectType
    {
        $name = static::NAME;
        $description = static::DESCRIPTION;
        $types = $context->getTypes();
        if (!$types) {
            throw new UndefinedTypesException();
        }
        $nodeType = $context->getTypes()->getOutput($nodeClassName);
        if (static::$instance === null) {
            static::$instance = ConnectionTypeFactory::createEdgeType($nodeType, $name, $description);
        }
        return static::$instance;
    }
    /**
     * Recupera una función que se utiliza como factory para un servicio
     * Para asignar el tipo del nodo utiliza $types->getOutput($nodeClassName)
     * El nodo debe ser una Entitidad Doctrine
     * @param IContextService $context
     * @param string $nodeClassName
     * @return callable
     */
    public static function getFactory(IContextService $context, string $nodeClassName): callable
    {
        return function ($sm) use ($context, $nodeClassName) {
            return static::get($context, $nodeClassName);
        };
    }
    private function __construct()
    {
    }
    private function __clone()
    {
    }
}
