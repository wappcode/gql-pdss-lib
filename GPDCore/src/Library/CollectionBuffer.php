<?php

declare(strict_types=1);

namespace GPDCore\Library;

use GPDCore\Library\QueryDecorator;
use GraphQL\Type\Definition\ResolveInfo;

class CollectionBuffer
{

    protected $ids = [];
    protected $result = [];
    protected $class;
    protected $joinProperty;
    protected $joinRelations = [];
    protected $processedIds = [];
    protected $joinClass;
    protected $queryDecorator;

    /**
     * @param string $class Clase de la entidad que tiene relación con otra
     * @param string $joinProperty  nombre de la propiedad de la relación 
     * @param array $joinRelations nombres de las propiedades que son a su vez relaciones de la entidad relacionada
     * @param string $joincClass Clase de la entidad relacionada
     * @param QueryDecorator  | null $queryDecorator  Acceso a función para modificar el query
     */
    public function __construct(string $class, string $joinProperty, array $joinRelations = [], string $joinClass = null, ?QueryDecorator $queryDecorator = null)
    {
        $this->class = $class;
        $this->joinProperty = $joinProperty;
        $this->joinRelations = $joinRelations;
        $this->joinClass = $joinClass;
        $this->queryDecorator = $queryDecorator;
    }



    public function add($id)
    {
        $this->ids[] = $id;
    }
    public  function get($id)
    {
        return $this->result[$id] ?? null;
    }

    /**
     * @TODO Modificar para que dependa de los argumentos o datos de la consulta agregar opciones de filtros y orden
     * 
     * Carga en el buffer los datos de todos los registros relacionados con los ids
     * El parametro decorator es una funcion que se le pasa como parametro un objeto QueryBuilder y
     * retorna un array con los registros solicitados, su utilidad consiste en poder validar, filtrar o transformar los
     * registros que se van a incluir en el buffer
     * Ejemplo: function(QueryBuilder $qb): array{return $qb->getQuery()->getArrayResult()}
     * @param callable|null $decorator
     * @return void
     */
    public  function loadBuffered($source, array $args, IContextService $context, ResolveInfo $info)
    {

        $processedIds = $this->processedIds;
        $uniqueIds = array_unique($this->ids);
        // Convierte los ids en tipo string para no tener problemas al ejecutar un query con id = 0 cuando la columna es un string
        $uniqueIds = array_map("strval", $uniqueIds);
        $ids = array_filter($uniqueIds, function ($id) use ($processedIds) {
            return !in_array($id, $processedIds);
        });
        if (empty($ids)) {
            return;
        }
        $this->processedIds = array_merge($this->processedIds, $ids);
        $entityManager = $context->getEntityManager();
        $entityColumnAssociations = !empty($this->joinClass) ? EntityAssociations::getWithJoinColumns($entityManager, $this->joinClass) : [];
        $finalRelations = !empty($this->joinRelations) ? $this->joinRelations : $entityColumnAssociations;
        $qb = $entityManager->createQueryBuilder()->from($this->class, "entity")
            ->leftJoin("entity.{$this->joinProperty}", $this->joinProperty)
            ->select(array("partial entity.{id}", $this->joinProperty));

        $qb = GeneralDoctrineUtilities::addRelationsToQuery($qb, $finalRelations, $this->joinProperty);
        if ($this->queryDecorator instanceof QueryDecorator) {
            $decorator = $this->queryDecorator->getDecorator();
            $qb = $decorator($qb, $source, $args, $context, $info);
        }
        $items = $qb->andWhere($qb->expr()->in('entity.id', ':ids'))
            ->setParameter(':ids', $ids);

        $items = $qb->getQuery()->getArrayResult();
        foreach ($items as $k => $item) {
            $this->result[$item["id"]] = $item[$this->joinProperty] ?? [];
        }
    }
}
