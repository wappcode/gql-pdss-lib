<?php

declare(strict_types=1);

namespace GPDCore\Infrastructure\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ManyToManyAssociationMapping;
use Doctrine\ORM\Mapping\OneToManyAssociationMapping;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;

use GPDCore\Infrastructure\Doctrine\EntityAssociation as EntityAssociationAlias;

class EntityMetadataHelper
{
    public static function getIdFieldName(EntityManager $entityManager, string $className): string
    {
        $metadata = $entityManager->getClassMetadata($className);

        if (empty($metadata->identifier)) {
            throw new RuntimeException("Entity {$className} has no identifier defined");
        }

        return $metadata->identifier[0];
    }

    public static function extractEntityId(EntityManager $entityManager, object $entity): mixed
    {
        $className = get_class($entity);
        $identifier = self::getIdFieldName($entityManager, $className);
        $reflectionClass = new ReflectionClass($entity);
        $methodName = 'get' . ucfirst($identifier);

        if (!$reflectionClass->hasMethod($methodName)) {
            return null;
        }

        $method = $reflectionClass->getMethod($methodName);

        if (!($method->getModifiers() & ReflectionMethod::IS_PUBLIC)) {
            return null;
        }

        return $method->invoke($entity);
    }

    public static function getJoinColumnAssociations(EntityManager $entityManager, string $className): array
    {
        $metadata = $entityManager->getClassMetadata($className);
        $associations = $metadata->associationMappings;

        $associations = array_filter(
            $associations,
            fn($association) => count($association->joinColumns ?? []) === 1
        );

        $associations = array_map(
            fn($association) => self::createAssociationFromMapping($entityManager, $association),
            $associations
        );

        return $associations;
    }

    public static function getCollectionAssociations(EntityManager $entityManager, string $className): array
    {
        $metadata = $entityManager->getClassMetadata($className);
        $associations = $metadata->associationMappings;

        $associations = array_filter(
            $associations,
            fn($association) => $association instanceof OneToManyAssociationMapping
                || $association instanceof ManyToManyAssociationMapping
        );

        $associations = array_map(
            fn($association) => self::createAssociationFromMapping($entityManager, $association),
            $associations
        );

        return $associations;
    }

    protected static function createAssociationFromMapping(EntityManager $entityManager, object $associationMapping): EntityAssociationAlias
    {
        $fieldName = $associationMapping->fieldName;
        $targetEntity = $associationMapping->targetEntity;
        $associationMetadata = $entityManager->getClassMetadata($targetEntity);

        if (empty($associationMetadata->identifier)) {
            throw new RuntimeException("Target entity {$targetEntity} has no identifier defined");
        }

        $identifier = $associationMetadata->identifier[0];

        return new EntityAssociationAlias($fieldName, $identifier, $targetEntity);
    }
}
