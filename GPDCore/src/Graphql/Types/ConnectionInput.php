<?php

declare(strict_types=1);

namespace GPDCore\Graphql\Types;

use GraphQL\Type\Definition\Type;
use GPDCore\Graphql\Types\QueryJoinType;
use GPDCore\Graphql\Types\QuerySortType;
use GPDCore\Graphql\Types\QueryFilterType;
use Laminas\ServiceManager\ServiceManager;
use GraphQL\Type\Definition\InputObjectType;

class ConnectionInput extends InputObjectType
{
    const SM_NAME = 'ConnectionInput';
    public function __construct(ServiceManager $serviceManager)
    {
        $config = [
            'name' => static::SM_NAME,
            'fields' => [
                'pagination' => [
                    'type' => $serviceManager->get(PaginationInput::SM_NAME)
                ],
                'filters' => [
                    'type' => Type::listOf($serviceManager->get(QueryFilterType::SM_NAME))
                ],
                'sorts' => [
                    'type' => Type::listOf($serviceManager->get(QuerySortType::SM_NAME))
                ],
                'joins' => [
                    'type' => Type::listOf($serviceManager->get(QueryJoinType::SM_NAME))
                ]
            ]
        ];
        parent::__construct($config);
    }
}
