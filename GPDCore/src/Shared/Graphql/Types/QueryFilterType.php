<?php

declare(strict_types=1);

namespace GPDCore\Shared\Graphql\Types;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

class QueryFilterType extends InputObjectType
{
    public const SM_NAME = 'FilterGroupInput';

    public function __construct()
    {
        $config = [
            'name' => 'FilterGroupInput',
            'fields' => [
                'groupLogic' => ['type' => Type::string()],
                'conditionsLogic' => ['type' => Type::string()],
                'conditions' => ['type' => Type::listOf(Type::nonNull(Type::string()))],
            ],
        ];

        parent::__construct($config);
    }
}
