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
     * 
     * @param EntityBuffer $buffer
     * @param string $property
     * @return callable
     */
    public static function createEntityResolver(EntityBuffer $buffer, string $property)
    {
        return function ($source, array $args, $context, ResolveInfo $info) use ($buffer, $property) {
            $entityManager = $context->getEntityManager();
            $className = $buffer->getClass();
            $idPropertyName = EntityUtilities::getFirstIdentifier($entityManager, $className);
            $id = $source[$property][$idPropertyName] ?? "0";
            $buffer->add($id);
            return new Deferred(function () use ($id, $source, $args, $context, $info, $buffer) {
                $buffer->loadBuffered($source, $args, $context, $info);
                $result = $buffer->get($id);
                return $result;
            });
        };
    }
    /**
     * Crea un collection resolver
     * IMPORTANTE asignar el valor de propertyRelations o joinClass no agrega los datos de las asociaciones si los dos son nulos
     *
     * @param string $mainClass
     * @param string $property
     * @param array|null $propertyRelations
     * @param string|null $joinClass
     * @return callable
     */
    public static function createCollectionResolver(string $mainClass, string $property, ?array $propertyRelations = null, string $joinClass = null)
    {
        $key = sprintf("%s::%s", $mainClass, $property);
        if (!isset(static::$buffers[$key])) {
            static::$buffers[$key] = new CollectionBuffer($mainClass, $property, $propertyRelations, $joinClass);
        }
        $buffer = static::$buffers[$key];
        return function ($source, $args, IContextService $context, $info) use ($buffer, $mainClass) {
            $entityManager = $context->getEntityManager();
            $idPropertyName = EntityUtilities::getFirstIdentifier($entityManager, $mainClass);
            $id = $source[$idPropertyName] ?? "0";
            $buffer->add($id);
            return new Deferred(function () use ($id, $source, $args, $context, $info, $buffer) {
                $buffer->loadBuffered($source, $args, $context, $info);
                $result = $buffer->get($id);
                return $result;
            });
        };
    }
}
