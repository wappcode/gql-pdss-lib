<?php
declare(strict_types=1);

namespace GPDCore\Graphql;

use Exception;
use Doctrine\ORM\Query;
use GraphQL\Doctrine\Types;
use Doctrine\ORM\QueryBuilder;
use GPDCore\Library\IContextService;
use GPDCore\Services\ConfigService;
use GraphQL\Type\Definition\ResolveInfo;
use GPDCore\Library\GeneralDoctrineUtilities;
use Doctrine\ORM\Tools\Pagination\Paginator;

class ConnectionQueryResponse {



    /**
     * Procesa el resultado 
     */
    public static function get(QueryBuilder $qb, $root, array $args, IContextService $context, ResolveInfo $info, array $relations = []) {
        $config = $context->getConfig();
        $appLimit = $config->get("query_limit");
        $total = self::getTotal($qb);
        $limit = self::getLimit($args, $appLimit);
        $offset = self::getOffset($args, $total);
        $nodes = self::getNodes($qb, $limit, $offset, $relations);
        $edges = self::nodesToEdges($nodes, $offset);
        $firstCursor = self::getFirstCursor($edges);
        $lastCursor = self::getLastCursor($edges, $offset);
        $hasNext = ($total > ($offset + count($edges) ));
        $hasPrev = ($offset > 0);
        
        return [
            'totalCount' => $total,
            'pageInfo' => [
                'hasPreviousPage' => $hasPrev,
                'hasNextPage' => $hasNext,
                'startCursor' => $firstCursor,
                'endCursor' => $lastCursor,
            ],
            'edges' => $edges
            
        ];
    }
    protected static function getLimit(array $args, ?int $appLimit) {
        $first = $args['pagination']['first'] ?? null;
        $last = $args['pagination']['last'] ?? null;
        if($first === null && $last === null) {
            throw new Exception('Se debe especificar el valor para first o last');
        }else if($first !== null && $first < 0) {
            throw new Exception('Valor incorrecto para first');
        } else if($last !== null && $last < 0) {
            throw new Exception('Valor incorrecto para last');
        }
        $limitArgs = ($last !== null) ? $last : $first;
        $limit = ($appLimit !== null) ? min($appLimit, $limitArgs) : $limitArgs ;
        return $limit;
    }
    protected static function getOffset($args, $total) {
        $before = $args['pagination']['before'] ?? '';
        $after = $args['pagination']['after'] ?? '';
        $cursor = !empty($args['pagination']['last']) ? $before : $after;
        $afterDecoded = decodeCursor($cursor);

        $offset = preg_match("/^\d+$/", "{$afterDecoded}") ? intval($afterDecoded) : 0;
        if(isset($args['pagination']['last']) && empty($before)) {
            $offset = $total;
        } 
        if(!empty($args['pagination']['last'])) {
            $offset = $offset - $args['pagination']['last'] - 1;
        }
        return $offset;

    }

    protected static function getNodes(QueryBuilder $qb, ?int $limit, int $offset, array $relations) {
        $qbList = clone $qb;
        $qbList->setMaxResults($limit);
        $qbList->setFirstResult($offset);
        $aliases =$qbList->getAllAliases();
        $query = $qbList->getQuery()->setHydrationMode(Query::HYDRATE_ARRAY);
        $paginator = new Paginator($query, $fetchJoinCollection = true);
        return $paginator;

    }

    protected static function getTotal(QueryBuilder $qb) {
        $qbList = clone $qb;
        $qbList->setMaxResults(1);
        $paginator = new Paginator($qbList, $fetchJoinCollection = true);
        $total = count($paginator);
        return $total;
    }

    protected static function nodesToEdges($nodes, $afterCursor) {
        $edges = [];
        foreach($nodes as $index => $node) {
            $cursor = encodeCursor($afterCursor + $index + 1);
            array_push($edges, [
                'cursor' => $cursor,
                'node' => $node
            ]);
        }
        return $edges;
    }
    protected static function getFirstCursor($edges) {
        return $edges[0]['cursor'] ?? '';
    }
    protected static function getLastCursor($edges, $afterCursor) {
        return $edges[count($edges) - 1]['cursor'] ?? '';
    }


}