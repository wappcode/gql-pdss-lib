<?php

namespace GPDCore\Library;

use Doctrine\ORM\QueryBuilder;
use Exception;

/**
 * Utilidades para aplicar filtros en un Query dependiendo de los parámetros de la solicitud http
 */
class QueryFilter
{

    const CONDITION_LIKE = 'LIKE';
    const CONDITION_BETWEEN = 'BETWEEN';
    const CONDITION_EQUAL = 'EQUAL';
    const CONDITION_IN = 'IN';
    const CONDITION_IS_NULL = 'IS_NULL';
    const LOGIC_AND = 'AND';
    const LOGIC_OR = 'OR';
    /**
     * Agrega filtros a un query doctrine
     * 
     * Ejemplo de formato de filtros
     * $filter = [
     *       [
     *           "groupLogic" => 'AND',
     *           "conditionsLogic" => 'AND',
     *           "conditions" => [
     *               [
     *                   "type" => 'like',
     *                   "value" => 'xxx',
     *                   "property" => 'xxxx',
     *                   "not" => true, // solo algunos tipos
     *                   "propertyAlias" => 'xxxx' // los joins se deben agregar previamente
     *               ]
     *           ]
     *       ]
     *   ];
     *
     * @param QueryBuilder $qb
     * @param array $filter
     * @return QueryBuilder
     */
    public static function addFilters(QueryBuilder $qb, array $filter): QueryBuilder
    {
        $qbCopy = clone $qb;
        if (empty($filter)) {
            return $qbCopy;
        }
        
        $alias = $qbCopy->getRootAliases()[0];
        $groups = $filter;
        
        if (empty($groups)) {
            return $qbCopy;
        }
        $conditionsGroup = array_map(function ($group) use ($alias, $qbCopy) {
            $condition = static::createGroupCondition($qbCopy, $group, $alias);
            return ["group" => $group, "condition" => $condition];
        }, $groups);

        $conditionGroupBase = array_shift($conditionsGroup);
        $conditionBase = $conditionGroupBase["condition"];
        foreach ($conditionsGroup as $groupCondition) {
            // no aplica la logica del primer grupo los demas se van agregando conforme a la logica del grupo que se va a agregar
            // Las condiciones se van combinando para formar (Todas las condiciones anteriores OR|AND Condición por agregar)
            $logic = $groupCondition["group"]["groupLogic"] ?? static::LOGIC_AND; 
            $conditionBase = static::combineConditions($qbCopy, $conditionBase, $groupCondition["condition"], $logic);
        }

        $qbCopy->andWhere($conditionBase);
        return $qbCopy;
    }

    protected static function createGroupCondition(QueryBuilder $qb, array $group, string $alias)
    {

        $conditionLogic = $group["conditionsLogic"] ?? static::LOGIC_AND;
        $conditions = $group["conditions"];
        if (empty($conditions)) {
            return "";
        }
        $conditionBase = array_shift($conditions);
        static::addConditionParameter($qb, $alias, $conditionBase);
        $conditionBaseQuery = static::createCondition($alias, $conditionBase, $qb);
        foreach ($conditions as $condition) {
            $conditionQuery = static::createCondition($alias, $condition, $qb);
            $conditionBaseQuery = static::combineConditions($qb, $conditionBaseQuery, $conditionQuery, $conditionLogic);
            static::addConditionParameter($qb, $alias, $condition);
        }
        return $conditionBaseQuery;
    }

    protected static function combineConditions(QueryBuilder $qb,  $conditionBase, $condition, $logic)
    {
        $updatedCondition = $conditionBase;
        if ($logic === static::LOGIC_OR) {
            $updatedCondition = $qb->expr()->orX($conditionBase, $condition);
        } else {
            $updatedCondition = $qb->expr()->andX($conditionBase, $condition);
        }
        return $updatedCondition;
    }

    protected static function addConditionParameter(QueryBuilder $qb, $alias, $condition)
    {
        
        $value = $condition["value"];
        if (empty($value) || !is_array($value)) {
            throw new Exception('La condición no tiene un valor adecuado');
        }
        if($condition["type"] === static::CONDITION_BETWEEN) {
            $parameters = static::getParameterKeyBetween($alias, $condition);
            $values = $condition["value"];
            $qb->setParameter($parameters[0], $values[0]);
            $qb->setParameter($parameters[1], $values[1]);
        } elseif($condition["type"] === static::CONDITION_IN) {
            $parameter = static::getParameterKey($alias, $condition);
            $values = $condition["value"];
            $qb->setParameter($parameter, $values);
        }
        elseif($condition["type"] !== static::CONDITION_IS_NULL) {
            $parameter = static::getParameterKey($alias, $condition);
            $qb->setParameter($parameter, $value[0]);

        }
    }

    protected static function createCondition(string $rootAlias, array $condition, QueryBuilder $qb): string
    {
        $alias = static::calculateAlias($rootAlias, $condition);
        $type = $condition["type"];
        if ($type === static::CONDITION_LIKE) {
            return static::createConditionLike($alias, $condition);
        }
        if ($type === static::CONDITION_EQUAL) {
            return static::createConditionEqual($alias, $condition);
        }
        if ($type === static::CONDITION_BETWEEN) {
            return static::createConditionBetween($alias, $condition);
        }
        if ($type === static::CONDITION_IN) {
            return static::createConditionIn($alias, $condition, $qb);
        }
        if ($type === static::CONDITION_IS_NULL) {
            return static::createConditionIn($alias, $condition, $qb);
        }
        throw new Exception('El tipo de condicion no existe');
    }

    protected static function createConditionLike(string $alias, array $condition): string
    {
        $column = $condition["property"];
        $not = $condition["not"] ?? false;
        $parameter = static::getParameterKey($alias, $condition);
        if ($not) {
            return sprintf("%s.%s not like %s", $alias, $column, $parameter);
        } else {
            return sprintf("%s.%s like %s", $alias, $column, $parameter);
        }
    }
    protected static function createConditionIn(string $alias, array $condition, QueryBuilder $qb): string
    {
        $column = $condition["property"];
        $not = $condition["not"] ?? false;
        $parameter = static::getParameterKey($alias, $condition);
        if ($not) {
            $property =sprintf("%s.%s", $alias, $column);
            return $qb->expr()->notIn($property, $parameter);
        } else {
            $property =sprintf("%s.%s", $alias, $column);
            return $qb->expr()->in($property, $parameter);
        }
    }
    protected static function createConditionIsNull(string $alias, array $condition, QueryBuilder $qb): string
    {
        $column = $condition["property"];
        $not = $condition["not"] ?? false;
        if ($not) {
            $property =sprintf("%s.%s", $alias, $column);
            return $qb->expr()->isNull($property);
        } else {
            $property =sprintf("%s.%s", $alias, $column);
            return $qb->expr()->isNotNull($property);
        }
    }
    protected static function createConditionBetween(string $alias, array $condition): string
    {
        $property = $condition["property"];
        $value = $condition["value"];
        if (!is_array($value) || count($value) !== 2) {
            throw new Exception('Formato incorrecto el valor debe ser un array de strings para condición Between');
        }
        $parameters = static::getParameterKeyBetween($alias, $condition);
        return sprintf("%s.%s BETWEEN %s and %s", $alias, $property, $parameters[0], $parameters[1]);
    }
    protected static function createConditionEqual(string $alias, array $condition): string
    {
        $column = $condition["property"];
        $not = $condition["not"] ?? false;
        $parameter = static::getParameterKey($alias, $condition);
        if ($not) {
            return sprintf("%s.%s != %s", $alias, $column, $parameter);
        } else {
            return sprintf("%s.%s = %s", $alias, $column, $parameter);
        }
    }

    protected static function getParameterKey(string $rootAlias, array $condition, string $postfix = ''): string
    {
        $alias = static::calculateAlias($rootAlias, $condition);
        $property = $condition["property"];
        $key = sprintf(":filter_%s_%s%s", $alias, $property, $postfix);
        return $key;
    }

    protected static function calculateAlias(string $rootAlias, array $condition): string {
        $alias = (isset($condition["propertyAlias"]) && !empty($condition["propertyAlias"])) ?  $condition["propertyAlias"] :$rootAlias ;
        return $alias;
    }
    protected static function getParameterKeyBetween(string $rootAlias, array $condition): array
    {
        $first = static::getParameterKey($rootAlias, $condition, "_fist");
        $second = static::getParameterKey($rootAlias, $condition, "_second");
        return [$first, $second];
    }
}
