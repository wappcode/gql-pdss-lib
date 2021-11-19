<?php

namespace GPDCore\Graphql;

use GraphQL\Doctrine\Definition\EntityID;
use ReflectionClass;
use ReflectionMethod;

class ArrayToEntity
{


    /**
     * Recupera el objeto entity agregándole los valores del array
     * Solo agrega los valores que coinciden con el metodo set de una propiedad del objeto
     */
    public static function apply($entity, array $array)
    {
        $class = new ReflectionClass($entity);
        foreach ($array as $k => $value) {
            $methodName = 'set' . ucfirst($k);
            $method = self::getMethod($class, $methodName);
            $finalValue = ($value instanceof EntityID) ? $value->getEntity() : $value;
            self::invokeMethod($entity, $method, $finalValue);
        }
        return $entity;
    }

    protected static function getMethod($class, $name)
    {
        if ($class->hasMethod($name)) {
            $method = $class->getMethod($name);
            if ($method->getModifiers() & ReflectionMethod::IS_PUBLIC) {
                return $method;
            }
        }
    }

    protected static function invokeMethod($entity, $method, $value) {
        if ($method) {
            $method->invoke($entity, $value);
        }
    }
}
