<?php 

declare(strict_types=1);

namespace GPDCore\Graphql\Types;

use GPDCore\Library\QueryFilter;
use GraphQL\Type\Definition\EnumType;

class QueryFilterConditionTypeValue extends EnumType {
    public function __construct()
    {
        $config = [
            'name' => 'QueryFilterConditionType',
            'values' => [
                QueryFilter::CONDITION_EQUAL,
                QueryFilter::CONDITION_LIKE,
                QueryFilter::CONDITION_BETWEEN,
                QueryFilter::CONDITION_IN,
                QueryFilter::CONDITION_IS_NULL,

            ],
        ];

        parent::__construct($config);
    }
}