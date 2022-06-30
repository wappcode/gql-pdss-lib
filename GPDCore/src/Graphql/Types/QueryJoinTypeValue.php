<?php 

declare(strict_types=1);

namespace GPDCore\Graphql\Types;

use PDSSUtilities\QueryJoins;
use GraphQL\Type\Definition\EnumType;

class QueryJoinTypeValue extends EnumType {
    
    const SM_NAME = "QueryJoinType";
    public function __construct()
    {
        $config = [
            'name' => 'QueryJoinType',
            'values' => [
               QueryJoins::INNER_JOIN,
               QueryJoins::LEFT_JOIN,

            ],
        ];

        parent::__construct($config);
    }
}