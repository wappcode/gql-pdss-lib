<?php 

declare(strict_types = 1);

namespace GPDCore\Library;

use GraphQL\Type\Definition\ResolveInfo;

class EntityBuffer {
    
    protected $ids = array();
    protected $result = array();
    protected $class;
    protected $relations = array();

    /**
     * @param string $class nombre de la clase que esta relacionada
     * @param string $relations nombres de las propiedades que son a su vez relaciones de la entidad relacionada
     */
    public function __construct(string $class, array $relations = [])
    {
        $this->class = $class;
        $this->relations = $relations;
    }


   
    public function add($id) {
        $this->ids[] = $id;
    }
    public  function get($id) {
        return $this->result[$id] ?? null;
    }

    /**
     * 
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
    public  function loadBuffered($source, array $args, IContextService $context, ResolveInfo $info) {
        
        if(!empty($this->result) || empty($this->ids)) {
            return;
        }
        $entityManager = $context->getEntityManager();
        $ids = array_unique($this->ids);
        $qb = $entityManager->createQueryBuilder()->from($this->class, "entity")
        ->select("entity")
        ;
        $qb = GeneralDoctrineUtilities::addRelationsToQuery($qb, $this->relations);
        $qb->andWhere($qb->expr()->in('entity.id', ':ids'))
        ->setParameter(':ids', $ids);
        $items = $qb->getQuery()->getArrayResult();
        foreach($items as $k => $item) {
            $this->result[$item["id"]] = $item ?? null;
        }
    }
}