<?php

declare(strict_types=1);

namespace GPDCore\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use GPDCore\Exceptions\EntityNotFoundException;
use GPDCore\Exceptions\InvalidIdException;

class QueryBuilderHelper
{


    /**
     * Agrega asociaciones a un query con sus claves primarias correspondientes.
     * Crea una copia del QueryBuilder para no modificar el original.
     *
     * @warning Solo debe utilizarse para agregar relaciones de una entidad.
     *          Para múltiples entidades, los alias pueden confundirse si hay propiedades con el mismo nombre.
     *
     * @param EntityManager $entityManager EntityManager de Doctrine
     * @param QueryBuilder $qb QueryBuilder a copiar y modificar
     * @param string $className Nombre completo de la clase de la entidad
     * @param array|null $associations Array de strings o EntityAssociation[] (null para obtener automáticamente)
     * @param string|null $alias Alias alternativo para agregar asociaciones desde un join específico
     * @return QueryBuilder Copia del QueryBuilder con las asociaciones agregadas
     */
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
                // si ya esta asignada la relación el select id se debe realizar desde donde se agrego la relación
                $qbCopy->addSelect("partial {$fieldName}.{{$identifier}}");
            } else {
                $qbCopy->addSelect("partial {$fieldName}.{{$identifier}}");
            }
        }

        return $qbCopy;
    }

    /**
     * Recupera un array con los datos de una entidad ORM por su ID.
     *
     * @param EntityManager $entityManager EntityManager de Doctrine
     * @param string $class Nombre completo de la clase de la entidad
     * @param mixed $id ID de la entidad a buscar
     * @param array|null $relations Array de relaciones a incluir (null para obtener automáticamente)
     * @return array Array con los datos de la entidad
     * @throws InvalidIdException Si el ID proporcionado está vacío o es inválido
     * @throws EntityNotFoundException Si no se encuentra la entidad con el ID proporcionado
     */
    public static function fetchById(EntityManager $entityManager, string $class, $id, ?array $relations = null): array
    {
        if (empty($id)) {
            throw new InvalidIdException();
        }

        $idPropertyName = EntityMetadataHelper::getIdFieldName($entityManager, $class);
        $qb = $entityManager->createQueryBuilder()->from($class, 'entity')
            ->andWhere("entity.{$idPropertyName} = :id")
            ->setParameter(':id', $id)
            ->select('entity');

        $qb = self::withAssociations($entityManager, $qb, $class, $relations);
        $result = $qb->getQuery()->getOneOrNullResult(Query::HYDRATE_ARRAY);

        if ($result === null) {
            throw new EntityNotFoundException();
        }

        return $result;
    }

    /**
     * Agrega una asociación al QueryBuilder (método helper privado para evitar duplicación).
     *
     * @param QueryBuilder $qb QueryBuilder a modificar
     * @param string $rootAlias Alias raíz desde donde agregar la relación
     * @param string $fieldName Nombre del campo de la relación
     * @param string $identifier Nombre del identificador (generalmente 'id')
     * @param array $aliases Array de alias existentes en el QueryBuilder
     */
    private static function addAssociationToQueryBuilder(QueryBuilder $qb, string $rootAlias, string $fieldName, string $identifier, array $aliases): void
    {
        if (!in_array($fieldName, $aliases)) {
            $qb->leftJoin("{$rootAlias}.{$fieldName}", $fieldName);
            $qb->addSelect("partial {$fieldName}.{{$identifier}}");
        } else {
            // Si ya está asignada la relación, el select debe realizarse desde donde se agregó la relación
            $qb->addSelect("partial {$fieldName}.{{$identifier}}");
        }
    }
}
