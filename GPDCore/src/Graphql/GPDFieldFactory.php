<?php

declare(strict_types=1);

namespace GPDCore\Graphql;

use Exception;
use Doctrine\ORM\Query;
use GPDCore\Library\IContextService;
use GraphQL\Type\Definition\Type;
use GPDCore\Graphql\ConnectionTypeFactory;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GPDCore\Graphql\ConnectionQueryResponse;
use GPDCore\Graphql\Types\QueryFilterType;
use GPDCore\Graphql\Types\QueryJoinType;
use GPDCore\Graphql\Types\QuerySortType;
use GPDCore\Library\GeneralDoctrineUtilities;
use GPDCore\Library\QueryFilter;
use GPDCore\Library\QueryJoins;
use GPDCore\Library\QuerySort;

class GPDFieldFactory
{


    /**
     * Recupera un resolver tipo query connection
     * $queryDecorator es una funcion que modifica el query acepta como parámetro un QueryBuilder y retorna una copia modificada function(QueryBuilder $qb);
     *
     * @param string $class
     * @param array $relations
     * @param callable|null $queryDecorator
     * @return callable
     */
    public static function buildResolverConnection( string $class, array $relations, ?callable $queryDecorator = null): callable {
        return function ($root, array $args, IContextService $context, ResolveInfo $info) use($class, $relations, $queryDecorator) {
            $types = $context->getTypes();
            $joins = $args["joins"] ?? [];
            $filters = $args["filter"] ?? [];
            $sorting = $args["sorting"] ?? [];
            $qb = $types->createFilteredQueryBuilder($class, [], []);
            $qb = QueryJoins::addJoins($qb, $joins); // se agregan primero los joins para que puedan ser utilizados por filters y orderby
            $qb = QueryFilter::addFilters($qb, $filters);
            $qb = QuerySort::addOrderBy($qb, $sorting);
            $qb = GeneralDoctrineUtilities::addRelationsToQuery($qb, $relations);
            if(is_callable($queryDecorator)) {
                $qb = $queryDecorator($qb, $root, $args, $context, $info);
            }
            return ConnectionQueryResponse::get($qb, $root, $args, $context, $info, $relations);
        };
    }

    /**
     * Crea un tipo connection para una entidad ORM
     *
     * @param IContextService $context
     * @param string $class
     * @param string $name
     * @param string $description
     * @param array $relations
     * @param callable|null $proxy // funcion que acepta como parametro un resolver y devuelve un resolver
     * @return array
     */
    public static function buildFieldConnection(
        IContextService $context, 
        ObjectType $connection, 
        string $class,
        array $relations = [],
        ?callable $proxy = null): array
    {
        $types = $context->getTypes();
        $serviceManager = $context->getServiceManager();
        $resolver = self::buildResolverConnection($class, $relations);
        $proxyResolver = is_callable($proxy) ? $proxy($resolver) : $resolver;
        return [
            'type' => $connection,
            'args' => [
                [
                    'name' => 'filter',
                    'type' => Type::listOf($serviceManager->get(QueryFilterType::SM_NAME)),
                ],
                [
                    'name' => 'sorting',
                    'type' => Type::listOf($serviceManager->get(QuerySortType::SM_NAME)),
                ],
                [
                    'name' => 'joins',
                    'type' => Type::listOf($serviceManager->get(QueryJoinType::SM_NAME)),
                ],
                [
                    'name' => 'pagination',
                    'type' => ConnectionTypeFactory::getPaginationInput()
                ]
            ],
            'resolve' => $proxyResolver,
        ];
    }

    
    /**
     * Recupera un resolver tipo query lista
     * $queryDecorator es una funcion que modifica el query acepta como parámetro un QueryBuilder y retorna una copia modificada function(QueryBuilder $qb);
     * @param IContextService $context
     * @param string $class
     * @param array $relations
     * @param callable|null $queryDecorator
     * @return callable
     */
    public static function buildResolverList(string $class, array $relations, ?callable $queryDecorator = null): callable {
        return function ($root, array $args, IContextService $context, ResolveInfo $info) use($class, $relations, $queryDecorator) {
            $types = $context->getTypes();
            $joins = $args["joins"] ?? [];
            $filters = $args["filter"] ?? [];
            $sorting = $args["sorting"] ?? [];
            $qb = $types->createFilteredQueryBuilder($class, [], []);
            $qb = QueryJoins::addJoins($qb, $joins); // se agregan primero los joins para que puedan ser utilizados por filters y orderby
            $qb = QueryFilter::addFilters($qb, $filters);
            $qb = QuerySort::addOrderBy($qb, $sorting);
            $limit = $context->getConfig()->get('query_limit');
            if($limit !== null) {
                $qb->setMaxResults($limit);
            }
            $qb = GeneralDoctrineUtilities::addRelationsToQuery($qb,$relations);
            if(is_callable($queryDecorator)) {
                $qb = $queryDecorator($qb, $root, $args, $context, $info);
            }
            return $qb->getQuery()->getArrayResult();
        };
        
    }

    /**
     * Crea un tipo lista para una entidad ORM
     *
     * @param IContextService $context
     * @param string $class
     * @param array $relations
     * @param callable|null $proxy // funcion que acepta como parametro un resolver y devuelve un resolver
     * @return array
     */
    public static function buildFieldList(IContextService $context, string $class, array $relations = [], ?callable $proxy = null): array
    {
        $types = $context->getTypes();
        $resolver = self::buildResolverList($class, $relations);
        $proxyResolver = is_callable($proxy) ? $proxy($resolver) : $resolver;
        $serviceManager = $context->getServiceManager();
        return [
            'type' => Type::listOf($types->getOutput($class)),
            'args' => [
                [
                    'name' => 'filter',
                    'type' => Type::listOf($serviceManager->get(QueryFilterType::SM_NAME)),
                ],
                [
                    'name' => 'sorting',
                    'type' => Type::listOf($serviceManager->get(QuerySortType::SM_NAME)),
                ],
                [
                    'name' => 'joins',
                    'type' => Type::listOf($serviceManager->get(QueryJoinType::SM_NAME)),
                ],
            ],
            'resolve' => $proxyResolver 
        ];
    }
   /**
    * Recupera un resolver tipo query item
    * $queryDecorator es una funcion que modifica el query acepta como parámetro un QueryBuilder y retorna una copia modificada function(QueryBuilder $qb);
    *
    * @param IContextService $context
    * @param string $class
    * @param array $relations
    * @param callable|null $queryDecorator
    * @return callable
    */
    public static function buildResolverItem( string $class, array $relations, ?callable $queryDecorator = null): callable {
        return function ($root, array $args, IContextService $context, ResolveInfo $info) use($class, $relations, $queryDecorator) {
            $types = $context->getTypes();
            $qb = $types->createFilteredQueryBuilder($class,  [], []);
            $id = $args["id"];
            $alias = $qb->getRootAliases()[0];
            $qb->andWhere("{$alias}.id = :id")
                ->setParameter(":id", $id);
            $qb = GeneralDoctrineUtilities::addRelationsToQuery($qb,$relations);
            if(is_callable($queryDecorator)) {
                $qb = $queryDecorator($qb, $root, $args, $context, $info);
            }
            return $qb->getQuery()->getOneOrNullResult(Query::HYDRATE_ARRAY);
        };
    }
    /**
     * Crea un tipo lista para una entidad ORM
     *
     * @param IContextService $context
     * @param string $class
     * @param array $relations
     * @param callable|null $proxy // funcion que acepta como parametro un resolver y devuelve un resolver
     * @return array
     */
    public static function buildFieldItem(IContextService $context, string $class, array $relations = [], ?callable $proxy = null): array
    {
        $types = $context->getTypes();
        $resolver = self::buildResolverItem($class,$relations);
        $proxyResolver = is_callable($proxy) ? $proxy($resolver) : $resolver;
        return [
            'type' => $types->getOutput($class),

            'args' => [
                [
                    'name' => 'id',
                    'type' => Type::nonNull(Type::id())
                ]
            ],
            'resolve' => $proxyResolver,
        ];
    }

    /**
     * Recupera un resolver tipo mutation create
     *
     * @param IContextService $context
     * @param string $class
     * @param array $relations
     * @return callable
     */
    public static function buildResolverCreate(string $class, array $relations): callable {
        return function ($root, array $args, IContextService $context, ResolveInfo $info) use($class, $relations) {
            $entityManager = $context->getEntityManager();
            $entity = new $class();
            $input = $args["input"];
            ArrayToEntity::apply($entity, $input); // carga los valores del array a la entidad
            $entityManager->persist($entity);
            $entityManager->flush();
            $result = GeneralDoctrineUtilities::getArrayEntityById($entityManager, $class, $entity->getId(), $relations);
            return $result;
        };
        
    }

    /**
     * Crea un tipo create para una entidad ORM
     *
     * @param IContextService $context
     * @param string $class
     * @param array $relations
     * @param callable|null $proxy // funcion que acepta como parametro un resolver y devuelve un resolver
     * @return array
     */
    public static function buildFieldCreate(IContextService $context, string $class, array $relations = [], ?callable $proxy = null): array
    {
        $types = $context->getTypes();
        $resolver = self::buildResolverCreate($class, $relations);
        $resolverProxy = is_callable($proxy) ? $proxy($resolver) : $resolver;
        return [
            'type' => Type::nonNull($types->getOutput($class)),
            'args' => [
                'input' => Type::nonNull($types->getInput($class)),
            ],
            'resolve' => $resolverProxy
        ];
    }

    /**
     * Recupera un resolver tipo mutation update
     *
     * @param IContextService $context
     * @param string $class
     * @param array $relations
     * @return callable
     */
    public static function buildResolverUpdate(string $class, array $relations): callable {
        return function ($root, array $args, IContextService $context, ResolveInfo $info) use($class, $relations) {
            $entityManager = $context->getEntityManager();
            $id = $args["id"];
            $input = $args["input"];
            $entity = $entityManager->getRepository($class)->find($id);
            ArrayToEntity::apply($entity, $input); // carga los valores del array a la entidad
            if (method_exists($entity, 'setUpdated')) {
                $entity->setUpdated();
            }
            $entityManager->persist($entity);
            $entityManager->flush();
            $result = GeneralDoctrineUtilities::getArrayEntityById($entityManager, $class, $entity->getId(), $relations);
            return $result;
        };
    }
    /**
     * Crea un tipo update para una entidad ORM
     *
     * @param IContextService $context
     * @param string $class
     * @param array $relations
     * @param callable|null $proxy // funcion que acepta como parametro un resolver y devuelve un resolver
     * @return array
     */
    public static function buildFieldUpdate(IContextService $context, string $class, array $relations = [], ?callable $proxy = null): array
    {
        $types = $context->getTypes();
        $resolver = self::buildResolverUpdate($class, $relations);
        $proxyResolver = is_callable($proxy) ? $proxy($resolver) : $resolver;
        return [
            'type' => Type::nonNull($types->getOutput($class)),
            'args' => [
                'id' => Type::nonNull(Type::id()),
                'input' => Type::nonNull($types->getPartialInput($class)),
            ],
            'resolve' => $proxyResolver,
        ];
    }


    /**
     * Aplica el resolve por default para eliminar una entidad
     * @return array
     */
    public static function buildResolverDelete(string $class, array $relations): callable {
        return function ($root, array $args, IContextService $context, ResolveInfo $info) use($class, $relations) {
            $entityManager = $context->getEntityManager();
            $id = intval($args["id"]);
            if(empty($id)) {
                throw new Exception("Id Inválido");
            }
            $entity = GeneralDoctrineUtilities::getArrayEntityById($entityManager, $class, $id, $relations);
    
            if(empty($entity)) {
                throw new Exception("Registro no encontrado");
            }
    
            $entityManager->createQueryBuilder()->delete($class, 'entity')->andWhere('entity.id = :id')
            ->setMaxResults(1)
            ->setParameter(':id', $id)->getQuery()->execute();
            $entityManager->flush();
    
            return $entity;

        };
    }

    /**
     * Crea un tipo delete para una entidad ORM
     *
     * @param IContextService $context
     * @param string $class
     * @param array $relations
     * @param callable|null $proxy // funcion que acepta como parametro un resolver y devuelve un resolver
     * @return array
     */
    public static function buildFieldDelete(IContextService $context, string $class, array $relations = [], ?callable $proxy = null)
    {
        $types = $context->getTypes();
        $resolver = self::buildResolverDelete($class, $relations);
        $proxyResolver = is_callable($proxy) ? $proxy($resolver) : $resolver;
        return [
            'type' => Type::nonNull($types->getOutput($class)),
            'args' => [
                'id' => Type::nonNull(Type::id()),
            ],
            'resolve' => $proxyResolver,
        ];
    }


    
    
}
