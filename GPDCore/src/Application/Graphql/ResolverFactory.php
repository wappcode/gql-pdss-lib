<?php

declare(strict_types=1);

namespace GPDCore\Application\Graphql;

use Doctrine\ORM\Query;
use Exception;
use GPDCore\Application\DataLoaders\EntityDataLoader;
use GPDCore\Contracts\AppContextInterface;
use GPDCore\Contracts\QueryModifierInterface;
use GPDCore\DataLoaders\CollectionCountDataLoader;
use GPDCore\DataLoaders\CollectionDataLoader;
use GPDCore\Doctrine\EntityHydrator;
use GPDCore\Doctrine\EntityMetadataHelper;
use GPDCore\Doctrine\QueryBuilderHelper;
use GPDCore\Exceptions\DuplicateKeyException;
use GPDCore\Exceptions\EntityNotFoundException;
use GPDCore\Exceptions\InvalidIdException;
use GPDCore\Exceptions\RelatedEntitiesExistException;
use GraphQL\Deferred;
use GraphQL\Type\Definition\ResolveInfo;
use PDSSUtilities\QueryFilter;
use PDSSUtilities\QueryJoins;
use PDSSUtilities\QuerySort;

class ResolverFactory
{
    protected static $buffers = [];

    public static function forEntity(EntityDataLoader $buffer, string $property): callable
    {
        return function ($source, array $args, $context, ResolveInfo $info) use ($buffer, $property) {
            $entityManager = $context->getEntityManager();
            $className = $buffer->getClass();
            $idPropertyName = EntityMetadataHelper::getIdFieldName($entityManager, $className);
            $id = $source[$property][$idPropertyName] ?? '0';
            $buffer->add($id);

            return new Deferred(function () use ($id, $source, $args, $context, $info, $buffer) {
                $buffer->loadBuffered($source, $args, $context, $info);
                $result = $buffer->get($id);

                return $result;
            });
        };
    }

    public static function forCollection(string $mainClass, string $property, ?string $joinClass = null, ?\GPDCore\Contracts\QueryModifierInterface $queryDecorator = null): callable
    {
        $key = sprintf('%s::%s', $mainClass, $property);
        if (!isset(static::$buffers[$key])) {
            static::$buffers[$key] = new CollectionDataLoader($mainClass, $property, $joinClass, $queryDecorator);
        }
        $buffer = static::$buffers[$key];

        return function ($source, $args, AppContextInterface $context, $info) use ($buffer, $mainClass) {
            $entityManager = $context->getEntityManager();
            $idPropertyName = EntityMetadataHelper::getIdFieldName($entityManager, $mainClass);
            $id = $source[$idPropertyName] ?? '0';
            $buffer->add($id);

            return new Deferred(function () use ($id, $source, $args, $context, $info, $buffer) {
                $buffer->loadBuffered($source, $args, $context, $info);
                $result = $buffer->get($id);

                return $result;
            });
        };
    }

    public static function forCollectionCount(string $mainClass, string $property, ?QueryModifierInterface $queryDecorator = null): callable
    {
        $key = sprintf('%s::%s::count', $mainClass, $property);
        if (!isset(static::$buffers[$key])) {
            static::$buffers[$key] = new CollectionCountDataLoader($mainClass, $property, $queryDecorator);
        }
        $buffer = static::$buffers[$key];

        return function ($source, $args, AppContextInterface $context, $info) use ($buffer, $mainClass) {
            $entityManager = $context->getEntityManager();
            $idPropertyName = EntityMetadataHelper::getIdFieldName($entityManager, $mainClass);
            $id = $source[$idPropertyName] ?? '0';
            $buffer->add($id);

            return new Deferred(function () use ($id, $source, $args, $context, $info, $buffer) {
                $buffer->loadBuffered($source, $args, $context, $info);
                $result = $buffer->get($id);

                return $result;
            });
        };
    }

    public static function forConnection(string $class, callable|QueryModifierInterface|null $queryDecorator = null): callable
    {
        return function ($root, array $args, AppContextInterface $context, ResolveInfo $info) use ($class, $queryDecorator) {
            $joins = $args['input']['joins'] ?? [];
            $filters = $args['input']['filters'] ?? [];
            $sorting = $args['input']['sorts'] ?? [];

            $entityManager = $context->getEntityManager();
            $relations = EntityMetadataHelper::getJoinColumnAssociations($entityManager, $class);
            $qb = $entityManager->createQueryBuilder()->from($class, 'entity')->select('entity');
            $qb = QueryJoins::addJoins($qb, $joins);
            $qb = QueryFilter::addFilters($qb, $filters);
            $qb = QuerySort::addOrderBy($qb, $sorting);
            $qb = QueryBuilderHelper::withAssociations($entityManager, $qb, $class);

            if ($queryDecorator !== null) {
                $qb = is_callable($queryDecorator)
                    ? $queryDecorator($qb, $root, $args, $context, $info)
                    : $qb;
            }

            return \GPDCore\Application\Internal\RelayConnectionBuilder::build($qb, $root, $args, $context, $info);
        };
    }

    public static function forList(string $class, QueryModifierInterface|callable|null $queryDecorator = null): callable
    {
        return function ($root, array $args, AppContextInterface $context, ResolveInfo $info) use ($class, $queryDecorator) {
            $joins = $args['input']['joins'] ?? [];
            $filters = $args['input']['filters'] ?? [];
            $sorting = $args['input']['sorts'] ?? [];

            $entityManager = $context->getEntityManager();
            $qb = $entityManager->createQueryBuilder()->from($class, 'entity')->select('entity');
            $qb = QueryJoins::addJoins($qb, $joins);
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

    public static function forItem(string $class, QueryModifierInterface|callable|null $queryDecorator = null): callable
    {
        return function ($root, array $args, AppContextInterface $context, ResolveInfo $info) use ($class, $queryDecorator) {
            $entityManager = $context->getEntityManager();
            $qb = $entityManager->createQueryBuilder()->from($class, 'entity')->select('entity');
            $idPropertyName = EntityMetadataHelper::getIdFieldName($entityManager, $class);
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

    public static function forCreate(string $class): callable
    {
        return function ($root, array $args, AppContextInterface $context, ResolveInfo $info) use ($class) {
            $entityManager = $context->getEntityManager();
            $entity = new $class();
            $input = $args['input'];
            EntityHydrator::hydrate($entityManager, $entity, $input);

            $entityManager->beginTransaction();

            try {
                $entityManager->persist($entity);
                $entityManager->flush();
                $entityManager->commit();
                $id = EntityMetadataHelper::extractEntityId($entityManager, $entity);
                $result = QueryBuilderHelper::fetchById($entityManager, $class, $id);

                return $result;
            } catch (Exception $e) {
                $entityManager->rollback();
                self::handleDatabaseException($e);
            }
        };
    }

    public static function forUpdate(string $class): callable
    {
        return function ($root, array $args, AppContextInterface $context, ResolveInfo $info) use ($class) {
            $entityManager = $context->getEntityManager();
            $id = $args['id'];
            $input = $args['input'];
            $entity = $entityManager->getRepository($class)->find($id);

            if (empty($entity) || !($entity instanceof $class)) {
                throw new EntityNotFoundException();
            }

            EntityHydrator::hydrate($entityManager, $entity, $input);
            if (method_exists($entity, 'setUpdated') && is_callable([$entity, 'setUpdated'])) {
                $entity->setUpdated();
            }
            $entityManager->beginTransaction();

            try {
                $entityManager->persist($entity);
                $entityManager->flush();
                $entityManager->commit();
                $id = EntityMetadataHelper::extractEntityId($entityManager, $entity);
                $result = QueryBuilderHelper::fetchById($entityManager, $class, $id);

                return $result;
            } catch (Exception $e) {
                $entityManager->rollback();
                self::handleDatabaseException($e);
            }
        };
    }

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

    private static function handleDatabaseException(Exception $e): void
    {
        $message = $e->getMessage();
        if (str_contains($message, 'SQLSTATE') && str_contains($message, 'Duplicate')) {
            throw new DuplicateKeyException(previous: $e);
        }

        throw $e;
    }

    private static function handleDeleteException(Exception $e): void
    {
        $message = $e->getMessage();
        if (str_contains($message, 'SQLSTATE') && str_contains($message, 'Cannot delete or update a parent row')) {
            throw new RelatedEntitiesExistException(previous: $e);
        }

        throw $e;
    }
}
