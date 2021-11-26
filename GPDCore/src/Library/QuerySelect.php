<?php

namespace Application\Library;

use Doctrine\ORM\QueryBuilder;
use Exception;

/**
 * Utilidades que calcula el valor de select para un query doctrine
 */
class QuerySelect
{

    /**
     * Recupera el valor para asignar al select de un query
     * 
     * Ejemplo de formato de filtros
     * $select = [
     *      'properties' => [],
     *      'joins' => 
     *       [
     *          [
     *              'joinedAlias' => 'XXXXX',
     *              'properties' => ['id','name'] 
     *          ],
     *          [
     *              'joinedAlias' => 'yyyyyy',
     *              'properties' => ['id','name'] 
     *          ]
     *      ]
     *      'partial joinAlias2.{id, ....}' 
     *   ];
     * 
     *
     * @param QueryBuilder $qb
     * @param array $select
     * @return QueryBuilder
     */
    public static function createDoctrineSelectValue(string $rootAlias, ?array $data): array
    {
        $select = [];
        $rootProperties = $data["properties"] ?? [];
        $select[] = static::calculateSelection($rootAlias, $rootProperties);
        $joins = $data['joins'] ?? [];
        if (!is_array($joins)) {
            return $select;
        }
        $joinsSelect = array_map(function($item) {
            $alias = $item["joinedAlias"];
            $properties = $item["properties"];
            if (empty($alias)) {
                return null;
            }
            return static::calculateSelection($alias, $properties);
        }, $joins);
        $joinsSelect = array_filter($joinsSelect, function($item){
            return !empty($item);
        });
        return array_merge($select, $joinsSelect);
    }

    public static function calculateSelection (string $alias, ?array $properties): string {
        if(!is_array($properties) || empty($properties)) {
            return $alias;
        } 
        $columns = implode(",", $properties);
        $partial = sprintf('partial %s.{%s}', $alias, $columns);
        return $partial;
    }

}
