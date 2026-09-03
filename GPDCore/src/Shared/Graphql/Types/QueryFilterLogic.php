<?php

declare(strict_types=1);

namespace GPDCore\Shared\Graphql\Types;

use GraphQL\Type\Definition\EnumType;

class QueryFilterLogic extends EnumType
{
    public const SM_NAME = 'FilterLogic';

    public function __construct()
    {
        $config = [
            'name' => 'FilterLogic',
            'values' => ['AND', 'OR'],
        ];

        parent::__construct($config);
    }
}
