<?php

namespace GPDCore\Library;

use Exception;
use Doctrine\ORM\QueryBuilder;

/**
 * Utilidades para aplicar filtros en un Query dependiendo de los parámetros de la solicitud http
 */
class QuerySort
{

    const DIRECTION_ASC = 'asc';
    const DIRECTION_DESC = 'desc';
    /**
     * Agrega filtros a un query doctrine
     * 
     * Ejemplo de formato de filtros
     * $orderBy = [
     *  [
     *      "direction": 'asc|desc'
     *      "property": 'xxxx',
     *      "joinedAlias" => 'xxxx' // los joins se deben agregar previamente
     *  ]
     * ]
     *
     * @param QueryBuilder $qb
     * @param array $orderBy
     * @return QueryBuilder
     */
    public static function addOrderBy(QueryBuilder $qb, array $orderBy): QueryBuilder
    {
        $qbCopy = clone $qb;
        if (empty($orderBy)) {
            return $qbCopy;
        }
        $rootAlias = $qbCopy->getRootAliases()[0];
        $orderByItems = array_filter($orderBy, function($item) {
            return !empty($item["property"]);
        });
        $orderByItems = array_map(function($item){
            return static::standardizeOrderByItem($item);
        }, $orderByItems);


        foreach($orderByItems as $item) {
            static::addOrderByItem($qbCopy, $item, $rootAlias);
        }
        return $qbCopy;
    }
     /**
     * Verifica y estandariza el formato de los datos para ordenar
     * Si es string lo convierte en array
     *
     * @param string | array $pagination
     * @return array
     */
    public static function standardizeRequestParams($orderBy): array {
        if (is_string($orderBy)) {
            return json_decode($orderBy, $assoc = true);
        } elseif(is_array($orderBy)) {
            return $orderBy;
        } else {
            throw new Exception('El formato de los datos para ordenar es incorrecto');
        }
    }

    private static function addOrderByItem(QueryBuilder $qb, array $item, $rootAlias) {
        $alias =   static::calculateAlias($rootAlias, $item);
        $column = sprintf('%s.%s', $alias, $item["property"]);
        $direction = $item["direction"];
        $qb->addOrderBy($column, $direction);

    }
    private static function standardizeOrderByItem(array $item) {
        $direction = empty($item["direction"]) ? static::DIRECTION_ASC : $item["direction"];
        $item["direction"] = $direction;
        return $item;
    }

    protected static function calculateAlias(string $rootAlias, array $item): string {
        $alias = (isset($item["joinedAlias"]) && !empty($item["joinedAlias"])) ?  $item["joinedAlias"] :$rootAlias ;
        return $alias;
    }
}
