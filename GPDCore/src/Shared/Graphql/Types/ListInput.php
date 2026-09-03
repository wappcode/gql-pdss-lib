<?php

declare(strict_types=1);

namespace GPDCore\Shared\Graphql\Types;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

class ListInput extends InputObjectType
{
    public const SM_NAME = 'ListInput';

    public function __construct()
    {
        $config = [
            'name' => 'ListInput',
            'fields' => [
                'values' => ['type' => Type::listOf(Type::string())],
            ],
        ];

        parent::__construct($config);
    }
}
