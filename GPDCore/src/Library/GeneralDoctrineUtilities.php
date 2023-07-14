<?php

declare(strict_types=1);

namespace GPDCore\Library;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;

class GeneralDoctrineUtilities
{

    /**
     * Crea una copia del query agregandole los alias con ids
     */
    public static function addRelationsToQuery(QueryBuilder $qb, $relations, ?string $alias = null): QueryBuilder
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
     */
    public static function addColumnAssociationToQuery(EntityManager $entityManager, QueryBuilder $qb, string $className, array $associations = [], ?string $alias = null): QueryBuilder
    {
        $qbCopy = clone $qb;
        $rootAlias = $alias ?? $qbCopy->getRootAliases()[0];
        $associations = !empty($associations) ? $associations : EntityAssociations::getWithJoinColumns($entityManager, $className);

        $aliases = $qbCopy->getAllAliases();
        foreach ($associations as $relation) {
            if (is_array($relation)) {
                $fieldName = $relation["fieldName"];
                $identifier = $relation["targetEntity"];
            } else {
                $fieldName = $relation;
                $identifier = "id";
            }
            if (!in_array($fieldName, $aliases)) {
                $qbCopy->leftJoin("{$rootAlias}.{$fieldName}", $fieldName);
                // si ya esta asignada la relación el select id se debe realizar desde donde se agrego la relación
                $qbCopy->addSelect("partial {$fieldName}.{ {$identifier} as id}");
            } else {
                $qbCopy->addSelect("partial {$fieldName}.{ {$identifier} as id}");
            }
        }
        return $qbCopy;
    }
    /**
     * Recupera un array con los datos de una entidad ORM
     *
     * @param EntityManager $entityManager
     * @param string $class
     * @param integer $id
     * @param array $relations
     * @return array
     */
    public static function getArrayEntityById(EntityManager $entityManager, string $class,  $id, array $relations): array
    {
        $qb = $entityManager->createQueryBuilder()->from($class, 'entity')
            ->andWhere("entity.id = :id")
            ->setParameter(':id', $id)
            ->select('entity');

        $qb = GeneralDoctrineUtilities::addColumnAssociationToQuery($entityManager, $qb, $class, $relations);
        $result = $qb->getQuery()->getOneOrNullResult(Query::HYDRATE_ARRAY);
        return $result;
    }
}
