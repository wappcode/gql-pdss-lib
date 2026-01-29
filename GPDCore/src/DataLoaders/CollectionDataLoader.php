<?php

declare(strict_types=1);

namespace GPDCore\DataLoaders;

use GPDCore\Contracts\AppContextInterface;
use GPDCore\Contracts\QueryModifierInterface;
use GPDCore\Doctrine\EntityUtilities;
use GPDCore\Doctrine\QueryBuilderHelper;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * DataLoader para cargar colecciones relacionadas de manera eficiente.
 * 
 * Acumula IDs de entidades y carga sus colecciones relacionadas en batch,
 * evitando el problema N+1. Implementa el patrón DataLoader de GraphQL.
 */
class CollectionDataLoader
{
    /** @var array<int|string> */
    protected array $ids = [];

    /** @var array<int|string, array> */
    protected array $result = [];

    protected string $class;

    protected string $joinProperty;

    /** @var array<int|string> */
    protected array $processedIds = [];

    protected ?string $joinClass;

    protected ?QueryModifierInterface $queryDecorator;

    /**
     * @param string $class Clase de la entidad que tiene relación con otra
     * @param string $joinProperty Nombre de la propiedad de la relación
     * @param string|null $joinClass Clase de la entidad relacionada
     * @param QueryModifierInterface|null $queryDecorator Modificador para personalizar el query
     */
    public function __construct(string $class, string $joinProperty, ?string $joinClass = null, ?QueryModifierInterface $queryDecorator = null)
    {
        $this->class = $class;
        $this->joinProperty = $joinProperty;
        $this->joinClass = $joinClass;
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
     * Obtiene una colección previamente cargada del buffer.
     * 
     * @return array Array con los elementos de la colección o array vacío si no existe
     */
    public function get(int|string $id): array
    {
        return $this->result[$id] ?? [];
    }

    /**
     * Carga en batch todas las colecciones relacionadas pendientes del buffer.
     * 
     * Este método carga de manera eficiente múltiples colecciones en una sola consulta,
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
            ->leftJoin("entity.{$this->joinProperty}", $this->joinProperty)
            ->select(["partial entity.{{$idPropertyName}}", $this->joinProperty]);

        if ($this->joinClass !== null) {
            $qb = QueryBuilderHelper::withAssociations($entityManager, $qb, $this->joinClass, $this->joinProperty);
        }

        if ($this->queryDecorator instanceof QueryModifierInterface) {
            $qb = ($this->queryDecorator)($qb, $source, $args, $context, $info);
        }

        $qb->andWhere($qb->expr()->in("entity.{$idPropertyName}", ':ids'))
            ->setParameter(':ids', $ids);

        $results = $qb->getQuery()->getArrayResult();

        foreach ($results as $item) {
            $this->result[$item[$idPropertyName]] = $item[$this->joinProperty] ?? [];
        }
    }
}
