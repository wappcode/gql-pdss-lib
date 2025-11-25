<?php

declare(strict_types=1);

namespace GPDCore\Graphql\Types;

use GraphQL\Type\Definition\EnumType;
use PDSSUtilities\QueryJoins;

class QueryJoinTypeValue extends EnumType
{
    public const SM_NAME = 'QueryJoinType';

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
