<?php

declare(strict_types=1);

namespace GPDCore\Application\Internal;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use GPDCore\Application\Contracts\AppContextInterface;
use GPDCore\Application\Exceptions\InvalidPaginationException;
use GraphQL\Type\Definition\ResolveInfo;

use function GPDCore\Application\Core\decodeCursor;
use function GPDCore\Application\Core\encodeCursor;

class RelayConnectionBuilder
{
    public static function build(QueryBuilder $qb, mixed $root, array $args, AppContextInterface $context, ResolveInfo $info): array
    {
        $paginationInput = $args['input']['pagination'] ?? [];
        $config = $context->getConfig();
        $appLimit = $config->get('query_limit');
        $total = self::countTotal($qb);
        $limit = self::resolveLimit($paginationInput, $appLimit);
        $offset = self::resolveOffset($paginationInput, $total);
        $nodes = self::fetchNodes($qb, $limit, $offset);
        $edges = self::createEdges($nodes, $offset);
        $firstCursor = self::extractFirstCursor($edges);
        $lastCursor = self::extractLastCursor($edges);
        $hasNext = ($total > ($offset + count($edges)));
        $hasPrev = ($offset > 0);

        return [
            'totalCount' => $total,
            'pageInfo' => [
                'hasPreviousPage' => $hasPrev,
                'hasNextPage' => $hasNext,
                'startCursor' => $firstCursor,
                'endCursor' => $lastCursor,
            ],
            'edges' => $edges,
        ];
    }

    protected static function resolveLimit(array $paginationInput, ?int $appLimit): int
    {
        $first = $paginationInput['first'] ?? null;
        $last = $paginationInput['last'] ?? null;

        if ($first === null && $last === null) {
            $first = 0;
        }

        if ($first !== null && $first < 0) {
            throw new InvalidPaginationException('Valor incorrecto para first: debe ser mayor o igual a 0');
        }

        if ($last !== null && $last < 0) {
            throw new InvalidPaginationException('Valor incorrecto para last: debe ser mayor o igual a 0');
        }

        $limitArgs = ($last !== null) ? $last : $first;
        $limit = ($appLimit !== null) ? min($appLimit, $limitArgs) : $limitArgs;

        return $limit;
    }

    protected static function resolveOffset(array $paginationInput, int $total): int
    {
        $before = $paginationInput['before'] ?? '';
        $after = $paginationInput['after'] ?? '';
        $last = $paginationInput['last'] ?? null;
        $cursor = !empty($last) ? $before : $after;
        $afterDecoded = decodeCursor($cursor);

        $offset = preg_match("/^\d+$/", "{$afterDecoded}") ? intval($afterDecoded) : 0;

        if ($last !== null && empty($before)) {
            $offset = $total;
        }

        if ($last !== null) {
            $offset = $offset - $last - 1;
        }

        return $offset;
    }

    protected static function fetchNodes(QueryBuilder $qb, ?int $limit, int $offset): Paginator
    {
        $qbList = clone $qb;
        $qbList->setMaxResults($limit);
        $qbList->setFirstResult($offset);
        $query = $qbList->getQuery()->setHydrationMode(Query::HYDRATE_ARRAY);
        $paginator = new Paginator($query, fetchJoinCollection: true);

        return $paginator;
    }

    protected static function countTotal(QueryBuilder $qb): int
    {
        $qbList = clone $qb;
        $qbList->setMaxResults(1);
        $paginator = new Paginator($qbList, fetchJoinCollection: true);

        return count($paginator);
    }

    protected static function createEdges(iterable $nodes, int $afterCursor): array
    {
        $edges = [];
        foreach ($nodes as $index => $node) {
            $cursor = encodeCursor($afterCursor + $index + 1);
            $edges[] = [
                'cursor' => $cursor,
                'node' => $node,
            ];
        }

        return $edges;
    }

    protected static function extractFirstCursor(array $edges): string
    {
        return $edges[0]['cursor'] ?? '';
    }

    protected static function extractLastCursor(array $edges): string
    {
        if (empty($edges)) {
            return '';
        }

        return $edges[count($edges) - 1]['cursor'];
    }
}
