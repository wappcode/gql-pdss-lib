<?php

declare(strict_types=1);

namespace GPDCore\Doctrine;

use GPDCore\Contracts\AppContextInterface;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Buffer para cargar múltiples entidades de manera eficiente.
 * 
 * Acumula IDs de entidades y las carga en batch para evitar el problema N+1.
 */
class EntityBuffer
{
    /** @var array<int|string> */
    protected array $ids = [];

    /** @var array<int|string, array|null> */
    protected array $result = [];

    protected string $class;

    /** @var array<int|string> */
    protected array $processedIds = [];

    /**
     * @param string $class Nombre completo de la clase de la entidad
     */
    public function __construct(string $class)
    {
        $this->class = $class;
    }

    /**
     * Agrega un ID al buffer para carga posterior.
     */
    public function add(int|string $id): void
    {
        $this->ids[] = $id;
    }

    /**
     * Obtiene una entidad previamente cargada del buffer.
     * 
     * @return array|null Array con los datos de la entidad o null si no existe
     */
    public function get(int|string $id): ?array
    {
        return $this->result[$id] ?? null;
    }

    /**
     * Carga en batch todas las entidades pendientes del buffer.
     * 
     * Este método carga de manera eficiente múltiples entidades en una sola consulta,
     * evitando el problema N+1. Solo carga los IDs que aún no han sido procesados.
     */
    public function loadBuffered(mixed $source, array $args, AppContextInterface $context, ResolveInfo $info): void
    {
        $uniqueIds = array_unique($this->ids);
        // Convierte los ids en tipo string para no tener problemas al ejecutar un query con id = 0 cuando la columna es un string
        $uniqueIds = array_map('strval', $uniqueIds);
        
        // Obtener solo los IDs que no han sido procesados
        $ids = array_diff($uniqueIds, $this->processedIds);
        
        if (empty($ids)) {
            return;
        }
        
        $this->processedIds = array_merge($this->processedIds, $ids);
        $entityManager = $context->getEntityManager();
        $idPropertyName = EntityUtilities::getFirstIdentifier($entityManager, $this->class);
        
        $qb = $entityManager->createQueryBuilder()
            ->from($this->class, 'entity')
            ->select('entity');
        
        $qb = QueryBuilderHelper::withAssociations($entityManager, $qb, $this->class);
        
        $qb->andWhere($qb->expr()->in("entity.{$idPropertyName}", ':ids'))
            ->setParameter(':ids', $ids);
        
        $items = $qb->getQuery()->getArrayResult();
        
        foreach ($items as $item) {
            $this->result[$item[$idPropertyName]] = $item;
        }
    }

    /**
     * Obtiene el nombre de la clase de entidad del buffer.
     */
    public function getClass(): string
    {
        return $this->class;
    }
}
