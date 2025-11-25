<?php

declare(strict_types=1);

namespace GPDCore\Graphql\Types;

use GraphQL\Type\Definition\EnumType;
use PDSSUtilities\QueryFilter;

class QueryFilterConditionTypeValue extends EnumType
{
    public const SM_NAME = 'QueryFilterConditionType';

    public function __construct()
    {
        $config = [
            'name' => static::SM_NAME,
            'values' => [
                QueryFilter::CONDITION_EQUAL,
                QueryFilter::CONDITION_NOT_EQUAL,
                QueryFilter::CONDITION_BETWEEN,
                QueryFilter::CONDITION_GREATER_THAN,
                QueryFilter::CONDITION_LESS_THAN,
                QueryFilter::CONDITION_GREATER_EQUAL_THAN,
                QueryFilter::CONDITION_LESS_EQUAL_THAN,
                QueryFilter::CONDITION_LIKE,
                QueryFilter::CONDITION_NOT_LIKE,
                QueryFilter::CONDITION_IN,
                QueryFilter::CONDITION_NOT_IN,
                QueryFilter::CONDITION_DIFFERENT,
                QueryFilter::CONDITION_IS_NOT_NULL,
                QueryFilter::CONDITION_IS_NULL,
            ],
        ];

        parent::__construct($config);
    }
}
