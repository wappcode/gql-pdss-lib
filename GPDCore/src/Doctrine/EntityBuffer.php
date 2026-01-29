<?php

declare(strict_types=1);

namespace GPDCore\Doctrine;


use GPDCore\Contracts\AppContextInterface;

use GraphQL\Type\Definition\ResolveInfo;

class EntityBuffer
{
    protected $ids = [];

    protected $result = [];

    protected $class;

    protected $relations = [];

    protected $processedIds = [];

    /**
     * @param string $class     nombre de la clase que esta relacionada
     * @param string $relations string[] | EntityAssociation[] nombres de las propiedades que son a su vez relaciones de la entidad relacionada
     */
    public function __construct(string $class, array $relations = [])
    {
        $this->class = $class;
        $this->relations = $relations;
    }

    public function add($id)
    {
        $this->ids[] = $id;
    }

    public function get($id)
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
     */
    public function loadBuffered($source, array $args, AppContextInterface $context, ResolveInfo $info)
    {
        $processedIds = $this->processedIds;
        $uniqueIds = array_unique($this->ids);
        // Convierte los ids en tipo string para no tener problemas al ejecutar un query con id = 0 cuando la columna es un string
        $uniqueIds = array_map('strval', $uniqueIds);
        $ids = array_filter($uniqueIds, function ($id) use ($processedIds) {
            return !in_array($id, $processedIds);
        });
        if (empty($ids)) {
            return;
        }
        $this->processedIds = array_merge($this->processedIds, $ids);
        $entityManager = $context->getEntityManager();
        $qb = $entityManager->createQueryBuilder()->from($this->class, 'entity')
            ->select('entity');
        $entityColumnAssociations = EntityUtilities::getColumnAssociations($entityManager, $this->class);
        $finalRelations = !empty($this->relations) ? $this->relations : $entityColumnAssociations;
        $idPropertyName = EntityUtilities::getFirstIdentifier($entityManager, $this->class);
        $qb = GeneralDoctrineUtilities::addColumnAssociationToQuery($entityManager, $qb, $this->class, $finalRelations);
        $qb->andWhere($qb->expr()->in("entity.{$idPropertyName}", ':ids'))
            ->setParameter(':ids', $ids);
        $items = $qb->getQuery()->getArrayResult();
        foreach ($items as $k => $item) {
            $this->result[$item[$idPropertyName]] = $item ?? null;
        }
    }

    /**
     * Get the value of class.
     */
    public function getClass()
    {
        return $this->class;
    }
}
