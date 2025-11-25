<?php

declare(strict_types=1);

namespace GPDCore\Graphql\Types;

use GraphQL\Type\Definition\EnumType;
use PDSSUtilities\QueryFilter;

class QueryFilterLogic extends EnumType
{
    public const SM_NAME = 'QueryFilterLogic';

    public function __construct()
    {
        $config = [
            'name' => static::SM_NAME,
            'values' => [
                QueryFilter::LOGIC_AND,
                QueryFilter::LOGIC_OR,
            ],
        ];

        parent::__construct($config);
    }
}
