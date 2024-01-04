<?php

declare(strict_types=1);

namespace GPDCore\Graphql\Types;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;

class PageInfoType extends ObjectType
{
    const SM_NAME = 'PageInfo';

    public function __construct()
    {
        $config = [
            'name' => 'PageInfo',
            'description' => 'Información para paginación',
            'fields' => [
                'hasPreviousPage' => Type::nonNull(Type::boolean()),
                'hasNextPage' =>   Type::nonNull(Type::boolean()),
                'startCursor' =>  Type::nonNull(Type::string()),
                'endCursor' =>  Type::nonNull(Type::string())
            ]
        ];
        parent::__construct($config);
    }
}
