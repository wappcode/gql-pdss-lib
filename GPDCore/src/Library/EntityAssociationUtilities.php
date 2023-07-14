<?php

namespace GPDCore\Library;

use ReflectionClass;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Entity;
use GPDCore\Library\EntityAssociation;

class EntityAssociationUtilities
{

    /**
     * Recupera un array con las propiedades de las asociaciones relacionadas con la entidad con una columna en la base de datos
     *  
     * @return array EntityAssociation[]
     */
    public static function getWithJoinColumns(EntityManager $entityManager, string $className): array
    {
        $metadata = $entityManager->getClassMetadata($className);

        $associations = $metadata->associationMappings;
        $associations = array_filter($associations, function ($association) {
            $joinColumns = $association["joinColumns"] ?? [];
            return count($joinColumns) === 1;
        });
        $associations = array_map(function ($association) use ($entityManager) {
            return static::createAssociationValue($entityManager, $association);
        }, $associations);

        return $associations;
    }

    /**
     * Recupera un array con las propiedades de las asociaciones relacionadas con la entidad que son de tipo collection
     *
     * @param EntityManager $entityManager
     * @param string $className
     * @return array EntityAssociation[]
     */
    public static function getCollections(EntityManager $entityManager, string $className): array
    {
        $metadata = $entityManager->getClassMetadata($className);

        $associations = $metadata->associationMappings;
        $associations = array_filter($associations, function ($association) use ($className) {

            $refl = new ReflectionClass($className);
            $property = $association["fieldName"];
            $propertyDocs = $refl->getProperty($property)->getDocComment();
            return preg_match("/(OneToMany\s*\()|(ManyToMany\s*\()/", $propertyDocs);
        });
        $associations = array_map(function ($association) use ($entityManager) {
            return static::createAssociationValue($entityManager, $association);
        }, $associations);

        return $associations;
    }

    /**
     * Crea un objeto EntityAssociation
     *
     * @param EntityManager $entityManager
     * @param array $association
     * @return array EntityAssociation[]
     */
    protected static function createAssociationValue(EntityManager $entityManager, array $association): EntityAssociation
    {
        $fieldName = $association["fieldName"];
        $targetEntity = $association["targetEntity"];
        $associationMetadata = $entityManager->getClassMetadata($targetEntity);
        $identifier = $associationMetadata->identifier[0];
        $result = new EntityAssociation($fieldName, $identifier, $targetEntity);
        return $result;
    }
}
