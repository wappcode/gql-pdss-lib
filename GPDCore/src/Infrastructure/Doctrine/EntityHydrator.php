<?php

declare(strict_types=1);

namespace GPDCore\Infrastructure\Doctrine;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use ReflectionClass;
use ReflectionMethod;

/**
 * Hydrator para poblar entidades Doctrine con datos de arrays.
 *
 * Mapea automáticamente propiedades de arrays a métodos setter de entidades,
 * incluyendo manejo especial para asociaciones de colecciones.
 */
class EntityHydrator
{
    public static function hydrate(EntityManager $entityManager, object $entity, array $data): object
    {
        $reflectionClass = new ReflectionClass($entity);
        $collectionAssociations = EntityMetadataHelper::getCollectionAssociations($entityManager, get_class($entity));
        $relations = EntityMetadataHelper::getJoinColumnAssociations($entityManager, get_class($entity));
        foreach ($data as $propertyName => $value) {
            $collectionAssociation = $collectionAssociations[$propertyName] ?? null;
            $relation = $relations[$propertyName] ?? null;

            if ($collectionAssociation !== null) {
                self::hydrateCollection($entityManager, $entity, $collectionAssociation, $value);
                continue;
            }
            if ($relation !== null) {
                $value = $entityManager->getReference($relation->getTargetEntity(), $value);
            }

            $methodName = 'set' . ucfirst($propertyName);
            $method = self::getMethod($reflectionClass, $methodName);
            self::invokeMethod($entity, $method, $value);
        }

        return $entity;
    }

    protected static function getMethod(ReflectionClass $reflectionClass, string $methodName): ?ReflectionMethod
    {
        if (!$reflectionClass->hasMethod($methodName)) {
            return null;
        }

        $method = $reflectionClass->getMethod($methodName);

        if (!($method->getModifiers() & ReflectionMethod::IS_PUBLIC)) {
            return null;
        }

        return $method;
    }

    protected static function invokeMethod(object $entity, ?ReflectionMethod $method, mixed $value): void
    {
        if ($method === null) {
            return;
        }

        $method->invoke($entity, $value);
    }

    protected static function hydrateCollection(
        EntityManager $entityManager,
        object $entity,
        \GPDCore\Infrastructure\Doctrine\EntityAssociation $relation,
        mixed $value
    ): void {
        $property = $relation->getFieldName();
        $reflectionClass = new ReflectionClass($entity);
        $methodName = 'get' . ucfirst($property);
        $method = self::getMethod($reflectionClass, $methodName);

        if ($method === null) {
            return;
        }

        /** @var Collection $collection */
        $collection = $method->invoke($entity);
        $collection->clear();

        if (empty($value) || !is_array($value)) {
            return;
        }

        $identifier = $relation->getIdentifier();
        $qb = $entityManager->createQueryBuilder()
            ->from($relation->getTargetEntity(), 'entity')
            ->select('entity');

        $qb->andWhere($qb->expr()->in("entity.{$identifier}", ':ids'))
            ->setParameter(':ids', $value);

        $result = $qb->getQuery()->getResult();

        foreach ($result as $item) {
            $collection->add($item);
        }
    }
}
