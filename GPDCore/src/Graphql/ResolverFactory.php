<?php

declare(strict_types=1);

namespace GPDCore\Graphql;

use GPDCore\Contracts\AppContextInterface;
use GPDCore\DataLoaders\CollectionDataLoader;
use GPDCore\DataLoaders\EntityDataLoader;
use GPDCore\Doctrine\ArrayToEntity;
use GPDCore\Doctrine\EntityUtilities;
use GPDCore\Contracts\QueryModifierInterface;
use GPDCore\Doctrine\QueryBuilderHelper;
use GPDCore\Exceptions\DuplicateKeyException;
use GPDCore\Exceptions\EntityNotFoundException;
use GPDCore\Exceptions\InvalidIdException;
use GPDCore\Exceptions\RelatedEntitiesExistException;
use Doctrine\ORM\Query;
use Exception;
use GraphQL\Deferred;
use GraphQL\Type\Definition\ResolveInfo;
use PDSSUtilities\QueryFilter;
use PDSSUtilities\QueryJoins;
use PDSSUtilities\QuerySort;

class ResolverFactory
{
    protected static $buffers = [];

    /**
     * NOTA cuando EntityDataLoader se utiliza en varias propiedades de diferentes Objetos
     * Deferred puede ser llamado con la consulta para un objeto y omitir las consultas de los demás objetos
     * Es necesario crear un EntityDataLoader para cada objeto.
     *
     * @return callable
     */
    public static function forEntity(EntityDataLoader $buffer, string $property): callable
    {
        return function ($source, array $args, $context, ResolveInfo $info) use ($buffer, $property) {
            $entityManager = $context->getEntityManager();
            $className = $buffer->getClass();
            $idPropertyName = EntityUtilities::getFirstIdentifier($entityManager, $className);
            $id = $source[$property][$idPropertyName] ?? '0';
            $buffer->add($id);

            return new Deferred(function () use ($id, $source, $args, $context, $info, $buffer) {
                $buffer->loadBuffered($source, $args, $context, $info);
                $result = $buffer->get($id);

                return $result;
            });
        };
    }

    /**
     * Crea un collection resolver
     * IMPORTANTE asignar el valor de propertyRelations o joinClass no agrega los datos de las asociaciones si los dos son nulos.
     *
     * @return callable
     */
    public static function forCollection(string $mainClass, string $property, ?string $joinClass = null): callable
    {
        $key = sprintf('%s::%s', $mainClass, $property);
        if (!isset(static::$buffers[$key])) {
            static::$buffers[$key] = new CollectionDataLoader($mainClass, $property, $joinClass);
        }
        $buffer = static::$buffers[$key];

        return function ($source, $args, AppContextInterface $context, $info) use ($buffer, $mainClass) {
            $entityManager = $context->getEntityManager();
            $idPropertyName = EntityUtilities::getFirstIdentifier($entityManager, $mainClass);
            $id = $source[$idPropertyName] ?? '0';
            $buffer->add($id);

            return new Deferred(function () use ($id, $source, $args, $context, $info, $buffer) {
                $buffer->loadBuffered($source, $args, $context, $info);
                $result = $buffer->get($id);

                return $result;
            });
        };
    }

    /**
     * Crea un resolver tipo query connection
     * $queryDecorator es una funcion que modifica el query acepta como parámetro un QueryBuilder y retorna una copia modificada function(QueryBuilder $qb);.
     *
     * @param callable|QueryModifierInterface|null $queryDecorator Acceso a función para modificar el query
     */
    public static function forConnection(string $class, callable|QueryModifierInterface|null $queryDecorator = null): callable
    {
        return function ($root, array $args, AppContextInterface $context, ResolveInfo $info) use ($class, $queryDecorator) {
            $joins = $args['input']['joins'] ?? [];
            $filters = $args['input']['filters'] ?? [];
            $sorting = $args['input']['sorts'] ?? [];

            $entityManager = $context->getEntityManager();
            $relations = EntityUtilities::getColumnAssociations($entityManager, $class);
            $qb = $entityManager->createQueryBuilder()->from($class, 'entity')->select('entity');
            $qb = QueryJoins::addJoins($qb, $joins); // se agregan primero los joins para que puedan ser utilizados por filters y orderby
            $qb = QueryFilter::addFilters($qb, $filters);
            $qb = QuerySort::addOrderBy($qb, $sorting);
            $qb = QueryBuilderHelper::withAssociations($entityManager, $qb, $class);
            
            if ($queryDecorator !== null) {
                $qb = is_callable($queryDecorator) 
                    ? $queryDecorator($qb, $root, $args, $context, $info)
                    : $qb;
            }

            return RelayConnectionBuilder::build($qb, $root, $args, $context, $info);
        };
    }

    /**
     * Crea un resolver tipo query lista
     * $queryDecorator es una funcion que modifica el query acepta como parámetro un QueryBuilder y retorna una copia modificada function(QueryBuilder $qb);.
     *
     * @param QueryModifierInterface|callable|null $queryDecorator Acceso a función para modificar el query
     */
    public static function forList(string $class, QueryModifierInterface|callable|null $queryDecorator = null): callable
    {
        return function ($root, array $args, AppContextInterface $context, ResolveInfo $info) use ($class, $queryDecorator) {
            $joins = $args['input']['joins'] ?? [];
            $filters = $args['input']['filters'] ?? [];
            $sorting = $args['input']['sorts'] ?? [];

            $entityManager = $context->getEntityManager();
            $qb = $entityManager->createQueryBuilder()->from($class, 'entity')->select('entity');
            $qb = QueryJoins::addJoins($qb, $joins); // se agregan primero los joins para que puedan ser utilizados por filters y orderby
            $qb = QueryFilter::addFilters($qb, $filters);
            $qb = QuerySort::addOrderBy($qb, $sorting);
            $limit = $context->getConfig()->get('query_limit');
            if ($limit !== null) {
                $qb->setMaxResults($limit);
            }
            $qb = QueryBuilderHelper::withAssociations($entityManager, $qb, $class);
            
            if ($queryDecorator !== null) {
                $qb = is_callable($queryDecorator)
                    ? $queryDecorator($qb, $root, $args, $context, $info)
                    : $qb;
            }

            return $qb->getQuery()->getArrayResult();
        };
    }

    /**
     * Crea un resolver tipo query item
     * $queryDecorator es una funcion que modifica el query acepta como parámetro un QueryBuilder y retorna una copia modificada function(QueryBuilder $qb);.
     *
     * @param QueryModifierInterface|callable|null $queryDecorator Acceso a función para modificar el query
     */
    public static function forItem(string $class, QueryModifierInterface|callable|null $queryDecorator = null): callable
    {
        return function ($root, array $args, AppContextInterface $context, ResolveInfo $info) use ($class, $queryDecorator) {
            $entityManager = $context->getEntityManager();
            $qb = $entityManager->createQueryBuilder()->from($class, 'entity')->select('entity');
            $idPropertyName = EntityUtilities::getFirstIdentifier($entityManager, $class);
            $id = $args['id'];
            $alias = $qb->getRootAliases()[0];
            $qb->andWhere("{$alias}.{$idPropertyName} = :id")
                ->setParameter(':id', $id);
            $qb = QueryBuilderHelper::withAssociations($entityManager, $qb, $class);
            
            if ($queryDecorator !== null) {
                $qb = is_callable($queryDecorator)
                    ? $queryDecorator($qb, $root, $args, $context, $info)
                    : $qb;
            }

            return $qb->getQuery()->getOneOrNullResult(Query::HYDRATE_ARRAY);
        };
    }

    /**
     * Crea un resolver tipo mutation create.
     */
    public static function forCreate(string $class): callable
    {
        return function ($root, array $args, AppContextInterface $context, ResolveInfo $info) use ($class) {
            $entityManager = $context->getEntityManager();
            $relations = EntityUtilities::getColumnAssociations($entityManager, $class);
            $entity = new $class();
            $input = $args['input'];
            ArrayToEntity::setValues($entityManager, $entity, $input); // carga los valores del array a la entidad

            $entityManager->beginTransaction();
            try {
                $entityManager->persist($entity);
                $entityManager->flush();
                $entityManager->commit();
                $id = EntityUtilities::getFirstIdentifierValue($entityManager, $entity);
                $result = QueryBuilderHelper::fetchById($entityManager, $class, $id, $relations);

                return $result;
            } catch (Exception $e) {
                $entityManager->rollback();
                self::handleDatabaseException($e);
            }
        };
    }

    /**
     * Crea un resolver tipo mutation update.
     */
    public static function forUpdate(string $class): callable
    {
        return function ($root, array $args, AppContextInterface $context, ResolveInfo $info) use ($class) {
            $entityManager = $context->getEntityManager();
            $relations = EntityUtilities::getColumnAssociations($entityManager, $class);
            $id = $args['id'];
            $input = $args['input'];
            $entity = $entityManager->getRepository($class)->find($id);

            if (empty($entity) || !($entity instanceof $class)) {
                throw new EntityNotFoundException();
            }

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
                $result = QueryBuilderHelper::fetchById($entityManager, $class, $id, $relations);

                return $result;
            } catch (Exception $e) {
                $entityManager->rollback();
                self::handleDatabaseException($e);
            }
        };
    }

    /**
     * Crea un resolver tipo mutation delete.
     */
    public static function forDelete(string $class): callable
    {
        return function ($root, array $args, AppContextInterface $context, ResolveInfo $info) use ($class) {
            $entityManager = $context->getEntityManager();
            $id = $args['id'];
            if (empty($id)) {
                throw new InvalidIdException();
            }
            $entity = $entityManager->find($class, $id);

            if (empty($entity) || !($entity instanceof $class)) {
                throw new EntityNotFoundException();
            }
            $entityManager->beginTransaction();

            try {
                $entityManager->remove($entity);
                $entityManager->flush();
                $entityManager->commit();

                return true;
            } catch (Exception $e) {
                $entityManager->rollback();
                self::handleDeleteException($e);
            }
        };
    }

    /**
     * Maneja excepciones de base de datos relacionadas con claves duplicadas.
     *
     * @throws DuplicateKeyException|Exception
     */
    private static function handleDatabaseException(Exception $e): void
    {
        $message = $e->getMessage();
        if (str_contains($message, 'SQLSTATE') && str_contains($message, 'Duplicate')) {
            throw new DuplicateKeyException(previous: $e);
        }
        throw $e;
    }

    /**
     * Maneja excepciones de base de datos relacionadas con eliminaciones.
     *
     * @throws RelatedEntitiesExistException|Exception
     */
    private static function handleDeleteException(Exception $e): void
    {
        $message = $e->getMessage();
        if (str_contains($message, 'SQLSTATE') && str_contains($message, 'Cannot delete or update a parent row')) {
            throw new RelatedEntitiesExistException(previous: $e);
        }
        throw $e;
    }
}
