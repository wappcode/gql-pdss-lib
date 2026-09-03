<?php

declare(strict_types=1);

namespace GPDCore\Application\DataLoaders;

use GPDCore\Application\Contracts\AppContextInterface;
use GPDCore\Application\Contracts\QueryModifierInterface;
use GPDCore\Infrastructure\Doctrine\EntityMetadataHelper;
use GraphQL\Type\Definition\ResolveInfo;

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

    public function __construct(string $class, string $joinProperty, ?QueryModifierInterface $queryDecorator = null)
    {
        $this->class = $class;
        $this->joinProperty = $joinProperty;
        $this->queryDecorator = $queryDecorator;
    }

    public function add(int|string $id): void
    {
        $this->ids[] = $id;
    }

    public function get(int|string $id): int
    {
        return $this->result[$id] ?? 0;
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

        foreach ($ids as $id) {
            if (!isset($this->result[$id])) {
                $this->result[$id] = 0;
            }
        }
    }
}
