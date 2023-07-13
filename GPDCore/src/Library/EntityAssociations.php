<?php

namespace GPDCore\Library;

use Doctrine\ORM\EntityManager;

class EntityAssociations
{

    /**
     * Recupera un array con las propiedades de las asociaciones relacionadas con la entidad con una columna en la base de datos
     *
     * @return array
     */
    public static function getWithJoinColumns(EntityManager $entityManager, string $className): array
    {
        $metadata = $entityManager->getClassMetadata($className);

        $associations = $metadata->associationMappings;
        $associations = array_filter($associations, function ($association) {
            $joinColumns = $association["joinColumns"] ?? [];
            return count($joinColumns) === 1;
        });
        $associations = array_keys($associations);
        return $associations;
    }
}
