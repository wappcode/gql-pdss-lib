<?php

declare(strict_types=1);

namespace GPDCore\Application\DataLoaders;

use GPDCore\Contracts\AppContextInterface;
use GPDCore\Contracts\QueryModifierInterface;
use GPDCore\Doctrine\EntityMetadataHelper;
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

    public readonly ?string $joinClass;

    protected ?QueryModifierInterface $queryDecorator;

    public function __construct(string $class, string $joinProperty, ?string $joinClass = null, ?QueryModifierInterface $queryDecorator = null)
    {
        $this->class = $class;
        $this->joinProperty = $joinProperty;
        $this->joinClass = $joinClass;
        $this->queryDecorator = $queryDecorator;
    }

    public function add(int|string $id): void
    {
        $this->ids[] = $id;
    }

    public function get(int|string $id): array
    {
        return $this->result[$id] ?? [];
    }

    public function loadBuffered(mixed $source, array $args, AppContextInterface $context, ResolveInfo $info): void
    {
        $uniqueIds = array_unique($this->ids);
        $uniqueIds = array_map('strval', $uniqueIds);

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
