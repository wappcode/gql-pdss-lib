<?php

declare(strict_types=1);

namespace GPDCore\Shared\Graphql\Types;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

class ConnectionInput extends InputObjectType
{
    public const SM_NAME = 'ConnectionInput';

    public function __construct()
    {
        $config = [
            'name' => 'ConnectionInput',
            'fields' => [
                'first' => ['type' => Type::int()],
                'after' => ['type' => Type::string()],
                'last' => ['type' => Type::int()],
                'before' => ['type' => Type::string()],
            ],
        ];

        parent::__construct($config);
    }
}
