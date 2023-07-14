<?php

namespace GPDCore\Graphql;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use GPDCore\Library\EntityAssociation;
use GPDCore\Library\EntityUtilities;
use GraphQL\Doctrine\Definition\EntityID;
use ReflectionClass;
use ReflectionMethod;

class ArrayToEntity
{


    /**
     * Recupera el objeto entity agregándole los valores del array
     * Solo agrega los valores que coinciden con el metodo set de una propiedad del objeto
     * @deprecated version 2.0.28 usar en su lugar la funciont setValues de esta misma clase
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
    /**
     * Recupera el objeto entity agregándole los valores del array
     * Solo agrega los valores que coinciden con el metodo set de una propiedad del objeto
     */
    public static function setValues(EntityManager $entityManager, $entity, array $array)
    {
        $class = new ReflectionClass($entity);
        $collectionAssociations = EntityUtilities::getCollections($entityManager, get_class($entity));
        foreach ($array as $k => $value) {
            $collectionAssociation = $collectionAssociations[$k] ?? null;
            if ($collectionAssociation) {
                static::updateCollectionAssociation($entityManager, $entity, $collectionAssociation, $value);
                continue;
            }
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

    protected static function invokeMethod($entity, $method, $value)
    {
        if ($method) {
            $method->invoke($entity, $value);
        }
    }



    protected static function updateCollectionAssociation(EntityManager $entityManager, $entity, EntityAssociation $relation, $value)
    {

        $property = $relation->getFieldName();
        $class = new ReflectionClass($entity);
        $methodName = 'get' . ucfirst($property);
        $method = self::getMethod($class, $methodName);
        if (!$method) {
            return;
        }
        /** @var Collection */
        $collection = $method->invoke($entity);
        $collection->clear();
        // si el valor esta vacío solo elimina todas las relaciones
        if (empty($value)) {
            return;
        }
        $identifier = $relation->getIdentifier();
        $qb = $entityManager->createQueryBuilder()->from($relation->getTargetEntity(), "entity")
            ->select("entity");
        $qb->andWhere($qb->expr()->in("entity.{$identifier}", ":ids"))
            ->setParameter(":ids", $value);
        $result = $qb->getQuery()->getResult();

        foreach ($result as $item) {
            $collection->add($item);
        }
    }
}
