<?php

declare(strict_types=1);

namespace GPDCore\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class GeneralDoctrineUtilities
{
    /**
     * Crea una copia del query agregandole los alias con ids
     * IMPORTANTE: Solo se debe utilizar para agregar las relaciones de un entidad, si se utiliza para múltiples hay la posibilidad que los alias se confundan cuando hay mas de una entidad con la misma propiead.
     *
     * @param string|null $alias Se utiliza para agregar las asociaciones de un join en lugar de la clase principal del query
     */
    public static function addRelationsToQuery(QueryBuilder $qb, array $relations, ?string $alias = null): QueryBuilder
    {
        $qbCopy = clone $qb;
        $rootAlias = $alias ?? $qbCopy->getRootAliases()[0];
        $aliases = $qbCopy->getAllAliases();
        foreach ($relations as $relation) {
            if (!in_array($relation, $aliases)) {
                $qbCopy->leftJoin("{$rootAlias}.{$relation}", $relation);
                // si ya esta asignada la relación el select id se debe realizar desde donde se agrego la relación
                $qbCopy->addSelect("partial {$relation}.{id}");
            } else {
                $qbCopy->addSelect("partial {$relation}.{id}");
            }
        }

        return $qbCopy;
    }

    /**
     * Crea una copia del query agregandole los alias con las claves primarias
     * IMPORTANTE: Solo se debe utilizar para agregar las relaciones de un entidad, si se utiliza para múltiples hay la posibilidad que los alias se confundan cuando hay mas de una entidad con la misma propiead.
     *
     * @param array       $associations string[] | EntityAssociation[]
     * @param string|null $alias        Se utiliza para agregar las asociaciones de un join en lugar de la clase principal del query
     */
    public static function addColumnAssociationToQuery(EntityManager $entityManager, QueryBuilder $qb, string $className, ?array $associations = null, ?string $alias = null): QueryBuilder
    {
        $qbCopy = clone $qb;
        $rootAlias = $alias ?? $qbCopy->getRootAliases()[0];
        $associations = !empty($associations) ? $associations : EntityUtilities::getColumnAssociations($entityManager, $className);

        $aliases = $qbCopy->getAllAliases();
        foreach ($associations as $relation) {
            if ($relation instanceof EntityAssociation) {
                $fieldName = $relation->getFieldName();
                $identifier = $relation->getIdentifier();
            } else {
                $fieldName = $relation;
                $identifier = 'id';
            }
            if (!in_array($fieldName, $aliases)) {
                $qbCopy->leftJoin("{$rootAlias}.{$fieldName}", $fieldName);
                // si ya esta asignada la relación el select id se debe realizar desde donde se agrego la relación
                $qbCopy->addSelect("partial {$fieldName}.{{$identifier}}");
            } else {
                $qbCopy->addSelect("partial {$fieldName}.{{$identifier}}");
            }
        }

        return $qbCopy;
    }

    /**
     * Recupera un array con los datos de una entidad ORM.
     *
     * @param int $id
     */
    public static function getArrayEntityById(EntityManager $entityManager, string $class, $id, ?array $relations = null): array
    {
        $idPropertyName = EntityUtilities::getFirstIdentifier($entityManager, $class);
        $qb = $entityManager->createQueryBuilder()->from($class, 'entity')
            ->andWhere("entity.{$idPropertyName} = :id")
            ->setParameter(':id', $id)
            ->select('entity');

        $qb = GeneralDoctrineUtilities::addColumnAssociationToQuery($entityManager, $qb, $class, $relations);
        $result = $qb->getQuery()->getOneOrNullResult(Query::HYDRATE_ARRAY);

        return $result;
    }
}
