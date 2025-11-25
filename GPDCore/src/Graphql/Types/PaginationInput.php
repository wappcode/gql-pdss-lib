<?php

namespace GPDCore\Graphql\Types;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

class PaginationInput extends InputObjectType
{
    public const SM_NAME = 'PaginationInput';

    public function __construct()
    {
        $config = [
            'name' => 'PaginationInput',
            'description' => 'Para obtener los siguientes elementos de la lista utilizar first,after y para obtener los elementos previos utilizar last, before',
            'fields' => [
                'first' => Type::int(),
                'after' => Type::string(),
                'last' => Type::int(),
                'before' => Type::string(),
            ],
        ];
        parent::__construct($config);
    }
}
