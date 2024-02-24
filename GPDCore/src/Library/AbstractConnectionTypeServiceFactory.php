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

    public static function get(?IContextService $context = null, ?string $edgeTypeName = null): ObjectType
    {
        if (empty($edgeTypeName)) {
            throw '$edgeTypeName is required';
        }
        $name = static::NAME;
        $description = static::DESCRIPTION;
        if (static::$instance === null) {
            $edgeType = $context->getServiceManager()->get($edgeTypeName);
            static::$instance = ConnectionTypeFactory::createConnectionType($context, $edgeType, $name, $description);
        }
        return static::$instance;
    }
    /**
     * Recupera una función que se utiliza como factory para un servicio
     * Para asignar el tipo del nodo utiliza $serviceManager->get($edgeTypeName)
     * En el serviceManager debe haber un servicio con el nombre igual a $edgeTypeName
     * @param IContextService $context
     * @param string $edgeTypeName
     * @return callable
     */
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
}
