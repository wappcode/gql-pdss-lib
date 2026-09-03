<?php

declare(strict_types=1);

namespace GPDCore\Shared\Graphql\Types;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

class PaginationInput extends InputObjectType
{
    public const SM_NAME = 'PaginationInput';

    public function __construct()
    {
        $config = [
            'name' => 'PaginationInput',
            'fields' => [
                'first' => ['type' => Type::int()],
                'after' => ['type' => Type::string()],
            ],
        ];

        parent::__construct($config);
    }
}
