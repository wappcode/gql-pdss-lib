<?php

declare(strict_types=1);

namespace GPDCore\DataLoaders;

use GPDCore\Contracts\AppContextInterface;
use GPDCore\Contracts\QueryModifierInterface;
use GPDCore\Doctrine\EntityMetadataHelper;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * DataLoader para contar elementos de colecciones relacionadas de manera eficiente.
 * 
 * Acumula IDs de entidades y obtiene el conteo de sus colecciones relacionadas en batch,
 * evitando el problema N+1. Implementa el patrón DataLoader de GraphQL.
 */
class CollectionCountDataLoader
{
    /** @var array<int|string> */
    protected array $ids = [];

    /** @var array<int|string, int> */
    protected array $result = [];

    protected string $class;

    protected string $joinProperty;

    /** @var array<int|string> */
    protected array $processedIds = [];

    protected ?QueryModifierInterface $queryDecorator;

    /**
     * @param string $class Clase de la entidad que tiene relación con otra
     * @param string $joinProperty Nombre de la propiedad de la relación
     * @param QueryModifierInterface|null $queryDecorator Modificador para personalizar el query
     */
    public function __construct(string $class, string $joinProperty, ?QueryModifierInterface $queryDecorator = null)
    {
        $this->class = $class;
        $this->joinProperty = $joinProperty;
        $this->queryDecorator = $queryDecorator;
    }

    /**
     * Agrega un ID al buffer para carga posterior.
     */
    public function add(int|string $id): void
    {
        $this->ids[] = $id;
    }

    /**
     * Obtiene el conteo de una colección previamente cargado del buffer.
     * 
     * @return int Número de elementos en la colección o 0 si no existe
     */
    public function get(int|string $id): int
    {
        return $this->result[$id] ?? 0;
    }

    /**
     * Carga en batch los conteos de todas las colecciones relacionadas pendientes del buffer.
     * 
     * Este método cuenta de manera eficiente múltiples colecciones en una sola consulta,
     * evitando el problema N+1. Solo procesa los IDs que aún no han sido procesados.
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
        $idPropertyName = EntityMetadataHelper::getIdFieldName($entityManager, $this->class);

        $qb = $entityManager->createQueryBuilder()
            ->from($this->class, 'entity')
            ->leftJoin("entity.{$this->joinProperty}", $this->joinProperty)
            ->select("entity.{$idPropertyName}", 'COUNT(' . $this->joinProperty . '.id) as total')
            ->groupBy("entity.{$idPropertyName}");

        if ($this->queryDecorator instanceof QueryModifierInterface) {
            $qb = ($this->queryDecorator)($qb, $source, $args, $context, $info);
        }

        $qb->andWhere($qb->expr()->in("entity.{$idPropertyName}", ':ids'))
            ->setParameter(':ids', $ids);

        $results = $qb->getQuery()->getScalarResult();

        foreach ($results as $item) {
            $this->result[$item[$idPropertyName]] = (int) $item['total'];
        }

        // Inicializar con 0 los IDs que no tienen resultados
        foreach ($ids as $id) {
            if (!isset($this->result[$id])) {
                $this->result[$id] = 0;
            }
        }
    }
}
