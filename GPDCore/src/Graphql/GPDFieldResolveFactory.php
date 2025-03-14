<?php

declare(strict_types=1);

namespace GPDCore\Graphql;

use Exception;
use Doctrine\ORM\Query;
use PDSSUtilities\QuerySort;
use PDSSUtilities\QueryJoins;
use PDSSUtilities\QueryFilter;
use GPDCore\Library\GQLException;
use GPDCore\Library\QueryDecorator;
use GPDCore\Library\EntityUtilities;
use GPDCore\Library\IContextService;
use GraphQL\Type\Definition\ResolveInfo;
use GPDCore\Graphql\ConnectionQueryResponse;
use GPDCore\Library\GeneralDoctrineUtilities;

class GPDFieldResolveFactory
{


    /**
     * Recupera un resolver tipo query connection
     * $queryDecorator es una funcion que modifica el query acepta como parámetro un QueryBuilder y retorna una copia modificada function(QueryBuilder $qb);
     *
     * @param string $class
     * @param null|callable|QueryDecorator | callable | null $queryDecorator  Acceso a función para modificar el query
     * @return callable
     */
    public static function buildForConnection(string $class, null|callable|QueryDecorator $queryDecorator = null): callable
    {
        return function ($root, array $args, IContextService $context, ResolveInfo $info) use ($class,  $queryDecorator) {
            $joins = $args["input"]["joins"] ?? [];
            $filters = $args["input"]["filters"] ?? [];
            $sorting = $args["input"]["sorts"] ?? [];

            $entityManager = $context->getEntityManager();
            $relations = EntityUtilities::getColumnAssociations($entityManager, $class);
            $qb = $entityManager->createQueryBuilder()->from($class, "entity")->select('entity');
            $qb = QueryJoins::addJoins($qb, $joins); // se agregan primero los joins para que puedan ser utilizados por filters y orderby
            $qb = QueryFilter::addFilters($qb, $filters);
            $qb = QuerySort::addOrderBy($qb, $sorting);
            $qb = GeneralDoctrineUtilities::addColumnAssociationToQuery($entityManager, $qb, $class, $relations);
            $finalQueryDecorator = ($queryDecorator instanceof QueryDecorator) ? $queryDecorator->getDecorator() : $queryDecorator;
            if (is_callable($finalQueryDecorator)) {
                $qb = $finalQueryDecorator($qb, $root, $args, $context, $info);
            }
            return ConnectionQueryResponse::get($qb, $root, $args, $context, $info, $relations);
        };
    }




    /**
     * Recupera un resolver tipo query lista
     * $queryDecorator es una funcion que modifica el query acepta como parámetro un QueryBuilder y retorna una copia modificada function(QueryBuilder $qb);
     * @param IContextService $context
     * @param string $class
     * @param QueryDecorator | callable | null $queryDecorator Acceso a función para modificar el query
     * @return callable
     */
    public static function buildForList(string $class, QueryDecorator | callable | null $queryDecorator = null): callable
    {
        return function ($root, array $args, IContextService $context, ResolveInfo $info) use ($class,  $queryDecorator) {
            $joins = $args["input"]["joins"] ?? [];
            $filters = $args["input"]["filters"] ?? [];
            $sorting = $args["input"]["sorts"] ?? [];

            $entityManager = $context->getEntityManager();
            $relations = EntityUtilities::getColumnAssociations($entityManager, $class);
            $qb = $entityManager->createQueryBuilder()->from($class, "entity")->select("entity");
            $qb = QueryJoins::addJoins($qb, $joins); // se agregan primero los joins para que puedan ser utilizados por filters y orderby
            $qb = QueryFilter::addFilters($qb, $filters);
            $qb = QuerySort::addOrderBy($qb, $sorting);
            $limit = $context->getConfig()->get('query_limit');
            if ($limit !== null) {
                $qb->setMaxResults($limit);
            }
            $qb = GeneralDoctrineUtilities::addColumnAssociationToQuery($entityManager, $qb, $class, $relations);
            $finalQueryDecorator = ($queryDecorator instanceof QueryDecorator) ? $queryDecorator->getDecorator() : $queryDecorator;
            if (is_callable($finalQueryDecorator)) {
                $qb = $finalQueryDecorator($qb, $root, $args, $context, $info);
            }
            return $qb->getQuery()->getArrayResult();
        };
    }


    /**
     * Recupera un resolver tipo query item
     * $queryDecorator es una funcion que modifica el query acepta como parámetro un QueryBuilder y retorna una copia modificada function(QueryBuilder $qb);
     *
     * @param IContextService $context
     * @param string $class
     * @param QueryDecorator | callable | null $queryDecorator Acceso a función para modificar el query
     * @return callable
     */
    public static function buildForItem(string $class, QueryDecorator | callable | null $queryDecorator = null): callable
    {
        return function ($root, array $args, IContextService $context, ResolveInfo $info) use ($class, $queryDecorator) {

            $entityManager = $context->getEntityManager();
            if (empty($relations)) {
                $relations = EntityUtilities::getColumnAssociations($entityManager, $class);
            }
            $qb = $entityManager->createQueryBuilder()->from($class, "entity")->select("entity");
            $idPropertyName = EntityUtilities::getFirstIdentifier($entityManager, $class);
            $id = $args["id"];
            $alias = $qb->getRootAliases()[0];
            $qb->andWhere("{$alias}.{$idPropertyName} = :id")
                ->setParameter(":id", $id);
            $qb = GeneralDoctrineUtilities::addColumnAssociationToQuery($entityManager, $qb, $class, $relations);
            $finalQueryDecorator = ($queryDecorator instanceof QueryDecorator) ? $queryDecorator->getDecorator() : $queryDecorator;
            if (is_callable($finalQueryDecorator)) {
                $qb = $finalQueryDecorator($qb, $root, $args, $context, $info);
            }
            return $qb->getQuery()->getOneOrNullResult(Query::HYDRATE_ARRAY);
        };
    }

    /**
     * Recupera un resolver tipo mutation create
     *
     * @param IContextService $context
     * @param string $class
     * @return callable
     */
    public static function buildforCreate(string $class): callable
    {
        return function ($root, array $args, IContextService $context, ResolveInfo $info) use ($class) {
            $entityManager = $context->getEntityManager();
            $relations = EntityUtilities::getColumnAssociations($entityManager, $class);
            $entity = new $class();
            $input = $args["input"];
            $entityManager->beginTransaction();
            ArrayToEntity::setValues($entityManager, $entity, $input); // carga los valores del array a la entidad
            try {
                $entityManager->persist($entity);
                $entityManager->flush();
                $entityManager->commit();
                $id = EntityUtilities::getFirstIdentifierValue($entityManager, $entity);
                $result = GeneralDoctrineUtilities::getArrayEntityById($entityManager, $class, $id, $relations);
                return $result;
            } catch (Exception $e) {
                $entityManager->rollback();
                $message = $e->getMessage();
                if (str_contains($message, "SQLSTATE") && str_contains($message, "Duplicate")) {
                    throw new GQLException("Duplicated Key");
                } else {
                    throw $e;
                }
            }
        };
    }

    /**
     * Recupera un resolver tipo mutation update
     *
     * @param IContextService $context
     * @param string $class
     * @return callable
     */
    public static function buildForUpdate(string $class): callable
    {
        return function ($root, array $args, IContextService $context, ResolveInfo $info) use ($class) {
            $entityManager = $context->getEntityManager();
            $relations = EntityUtilities::getColumnAssociations($entityManager, $class);
            $id = $args["id"];
            $input = $args["input"];
            $entity = $entityManager->getRepository($class)->find($id);
            ArrayToEntity::setValues($entityManager, $entity, $input); // carga los valores del array a la entidad
            if (method_exists($entity, 'setUpdated')) {
                $entity->setUpdated();
            }
            $entityManager->beginTransaction();
            try {

                $entityManager->persist($entity);
                $entityManager->flush();
                $entityManager->commit();
                $id = EntityUtilities::getFirstIdentifierValue($entityManager, $entity);
                $result = GeneralDoctrineUtilities::getArrayEntityById($entityManager, $class, $id, $relations);
                return $result;
            } catch (Exception $e) {
                $entityManager->rollback();
                $message = $e->getMessage();
                if (str_contains($message, "SQLSTATE") && str_contains($message, "Duplicate")) {
                    throw new GQLException("Duplicated Key");
                } else {
                    throw $e;
                }
            }
        };
    }

    /**
     * Aplica el resolve por default para eliminar una entidad
     * @return array
     */
    public static function buildForDelete(string $class): callable
    {
        return function ($root, array $args, IContextService $context, ResolveInfo $info) use ($class) {
            $entityManager = $context->getEntityManager();
            $relations = EntityUtilities::getColumnAssociations($entityManager, $class);
            $idPropertyName = EntityUtilities::getFirstIdentifier($entityManager, $class);
            $id = $args["id"];
            if (empty($id)) {
                throw new Exception("Id Inválido");
            }
            $entity = GeneralDoctrineUtilities::getArrayEntityById($entityManager, $class, $id, $relations);

            if (empty($entity)) {
                throw new Exception("Registro no encontrado");
            }
            $entityManager->beginTransaction();
            try {
                $entityManager->createQueryBuilder()->delete($class, 'entity')->andWhere("entity.{$idPropertyName} = :id")
                    ->setMaxResults(1)
                    ->setParameter(':id', $id)->getQuery()->execute();
                $entityManager->flush();
                $entityManager->commit();
                return true;
            } catch (Exception $e) {
                $entityManager->rollback();
                $message = $e->getMessage();
                if (str_contains($message, "SQLSTATE") && str_contains($message, "Cannot delete or update a parent row")) {
                    throw new GQLException("Related elements must be deleted first");
                } else {
                    throw $e;
                }
            }
        };
    }
}
