<?php

declare(strict_types=1);

namespace GPDCore\Graphql\Types;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

class QueryFilterConditionValueType extends InputObjectType
{
    const SM_NAME = 'QueryFilterConditionValue';
    public function __construct()
    {
        $config = [
            'name' => static::SM_NAME,
            'fields' => [
                'single' => [
                    'type' => Type::string(),
                ],
                'many' => [
                    'type' => Type::listOf(Type::string()),
                ],

            ]
        ];

        parent::__construct($config);
    }
}
