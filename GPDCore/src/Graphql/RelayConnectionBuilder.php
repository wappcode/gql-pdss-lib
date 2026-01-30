<?php

declare(strict_types=1);

namespace GPDCore\Graphql;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use GPDCore\Contracts\AppContextInterface;
use GPDCore\Exceptions\InvalidPaginationException;
use GraphQL\Type\Definition\ResolveInfo;

use function GPDCore\Functions\decodeCursor;
use function GPDCore\Functions\encodeCursor;

class RelayConnectionBuilder
{
    /**
     * Construye una respuesta de conexión paginada para GraphQL siguiendo el estándar Relay.
     *
     * @param QueryBuilder        $qb      QueryBuilder con la consulta base
     * @param mixed               $root    Valor raíz del resolver
     * @param array               $args    Argumentos de GraphQL
     * @param AppContextInterface $context Contexto de la aplicación
     * @param ResolveInfo         $info    Información de resolución de GraphQL
     *
     * @return array Array con estructura de conexión (totalCount, pageInfo, edges)
     */
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

    /**
     * Resuelve el límite de resultados basado en los parámetros de paginación.
     *
     * @param array    $paginationInput Parámetros de paginación (first, last)
     * @param int|null $appLimit        Límite máximo de la aplicación
     *
     * @return int Límite calculado
     *
     * @throws InvalidPaginationException Si los valores de paginación son negativos
     */
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

    /**
     * Resuelve el offset basado en los cursores de paginación.
     *
     * @param array $paginationInput Parámetros de paginación (before, after, last)
     * @param int   $total           Total de registros
     *
     * @return int Offset calculado
     */
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

    /**
     * Obtiene los nodos (resultados) de la consulta con paginación desde la base de datos.
     *
     * @param QueryBuilder $qb     QueryBuilder base
     * @param int|null     $limit  Límite de resultados
     * @param int          $offset Offset para la paginación
     *
     * @return Paginator Paginador con los resultados
     */
    protected static function fetchNodes(QueryBuilder $qb, ?int $limit, int $offset): Paginator
    {
        $qbList = clone $qb;
        $qbList->setMaxResults($limit);
        $qbList->setFirstResult($offset);
        $query = $qbList->getQuery()->setHydrationMode(Query::HYDRATE_ARRAY);
        $paginator = new Paginator($query, fetchJoinCollection: true);

        return $paginator;
    }

    /**
     * Cuenta el total de registros de la consulta.
     *
     * @param QueryBuilder $qb QueryBuilder base
     *
     * @return int Total de registros
     */
    protected static function countTotal(QueryBuilder $qb): int
    {
        $qbList = clone $qb;
        $qbList->setMaxResults(1);
        $paginator = new Paginator($qbList, fetchJoinCollection: true);

        return count($paginator);
    }

    /**
     * Crea los edges a partir de los nodos obtenidos.
     *
     * @param iterable $nodes       Nodos (resultados) a convertir en edges
     * @param int      $afterCursor Cursor inicial para calcular posiciones
     *
     * @return array Array de edges con cursor y node
     */
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

    /**
     * Extrae el cursor del primer edge.
     *
     * @param array $edges Array de edges
     *
     * @return string Cursor del primer elemento o cadena vacía si no hay edges
     */
    protected static function extractFirstCursor(array $edges): string
    {
        return $edges[0]['cursor'] ?? '';
    }

    /**
     * Extrae el cursor del último edge.
     *
     * @param array $edges Array de edges
     *
     * @return string Cursor del último elemento o cadena vacía si no hay edges
     */
    protected static function extractLastCursor(array $edges): string
    {
        if (empty($edges)) {
            return '';
        }

        return $edges[count($edges) - 1]['cursor'];
    }
}
