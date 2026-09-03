<?php

declare(strict_types=1);

namespace GPDCore\Infrastructure\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use GPDCore\Application\Exceptions\EntityNotFoundException;
use GPDCore\Application\Exceptions\InvalidIdException;

class QueryBuilderHelper
{
    public static function withAssociations(EntityManager $entityManager, QueryBuilder $qb, string $className, ?string $alias = null): QueryBuilder
    {
        $qbCopy = clone $qb;
        $rootAlias = $alias ?? $qbCopy->getRootAliases()[0];
        $associations = EntityMetadataHelper::getJoinColumnAssociations($entityManager, $className);

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
                $qbCopy->addSelect("partial {$fieldName}.{{$identifier}}");
            } else {
                $qbCopy->addSelect("partial {$fieldName}.{{$identifier}}");
            }
        }

        return $qbCopy;
    }

    public static function fetchById(EntityManager $entityManager, string $class, $id): array
    {
        if (empty($id)) {
            throw new InvalidIdException();
        }

        $idPropertyName = EntityMetadataHelper::getIdFieldName($entityManager, $class);
        $qb = $entityManager->createQueryBuilder()->from($class, 'entity')
            ->andWhere("entity.{$idPropertyName} = :id")
            ->setParameter(':id', $id)
            ->select('entity');

        $qb = self::withAssociations($entityManager, $qb, $class);
        $result = $qb->getQuery()->getOneOrNullResult(Query::HYDRATE_ARRAY);

        if ($result === null) {
            throw new EntityNotFoundException();
        }

        return $result;
    }

    private static function addAssociationToQueryBuilder(QueryBuilder $qb, string $rootAlias, string $fieldName, string $identifier, array $aliases): void
    {
        if (!in_array($fieldName, $aliases)) {
            $qb->leftJoin("{$rootAlias}.{$fieldName}", $fieldName);
            $qb->addSelect("partial {$fieldName}.{{$identifier}}");
        } else {
            $qb->addSelect("partial {$fieldName}.{{$identifier}}");
        }
    }
}
