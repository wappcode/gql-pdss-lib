<?php

namespace GPDCore\Library;

use GraphQL\Deferred;
use GraphQL\Type\Definition\ResolveInfo;

class ResolverFactory
{

    protected static $buffers = [];
    /**
     * NOTA cuando EntityBuffer se utiliza en varias propiedades de diferentes Objetos
     * Deferred puede ser llamado con la consulta para un objeto y omitir las consultas de los demás objetos
     * Es necesario crear un EntityBuffer para cada objeto
     */
    public static function createEntityResolver(EntityBuffer $buffer, string $property)
    {
        return function ($source, array $args, $context, ResolveInfo $info) use ($buffer, $property) {
            $id = $source[$property]['id'] ?? "0";
            $buffer->add($id);
            return new Deferred(function () use ($id, $source, $args, $context, $info, $buffer) {
                $buffer->loadBuffered($source, $args, $context, $info);
                $result = $buffer->get($id);
                return $result;
            });
        };
    }
    public static function createCollectionResolver(string $mainClass, string $property, array $propertyRelations, string $joinClass = null)
    {
        $key = sprintf("%s::%s", $mainClass, $property);
        if (!isset(static::$buffers[$key])) {
            static::$buffers[$key] = new CollectionBuffer($mainClass, $property, $propertyRelations, $joinClass);
        }
        $buffer = static::$buffers[$key];
        return function ($source, $args, $context, $info) use ($buffer) {
            $id = $source['id'] ?? "0";
            $buffer->add($id);
            return new Deferred(function () use ($id, $source, $args, $context, $info, $buffer) {
                $buffer->loadBuffered($source, $args, $context, $info);
                $result = $buffer->get($id);
                return $result;
            });
        };
    }
}
