<?php

namespace GPDCore\Library;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Entity;

class EntityAssociations
{

    /**
     * Recupera un array con las propiedades de las asociaciones relacionadas con la entidad con una columna en la base de datos
     *  
     * @return array [fieldName, identifier, targetEntity]
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
        $associations = array_values($associations);
        return $associations;
    }

    protected static function createAssociationValue(EntityManager $entityManager, array $association)
    {
        $fieldName = $association["fieldName"];
        $targetEntity = $association["targetEntity"];
        $associationMetadata = $entityManager->getClassMetadata($targetEntity);
        $identifier = $associationMetadata->identifier[0];
        $result = [
            "fieldName" => $fieldName,
            "identifier" => $identifier,
            "targetEntity" => $targetEntity
        ];
        return $result;
    }
}
