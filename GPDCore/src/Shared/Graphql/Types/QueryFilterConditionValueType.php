<?php

declare(strict_types=1);

namespace GPDCore\Shared\Graphql\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class QueryFilterConditionValueType extends ObjectType
{
    public const SM_NAME = 'QueryFilterConditionValueType';

    public function __construct()
    {
        $config = [
            'name' => 'QueryFilterConditionValueType',
            'fields' => [
                'single' => ['type' => Type::string()],
                'from' => ['type' => Type::string()],
                'to' => ['type' => Type::string()],
                'list' => ['type' => Type::listOf(Type::string())],
            ],
        ];

        parent::__construct($config);
    }
}
