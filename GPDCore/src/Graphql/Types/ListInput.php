<?php

declare(strict_types=1);

namespace GPDCore\Graphql\Types;

use GraphQL\Type\Definition\Type;
use GPDCore\Graphql\Types\QueryJoinType;
use GPDCore\Graphql\Types\QuerySortType;
use GPDCore\Graphql\Types\QueryFilterType;
use Laminas\ServiceManager\ServiceManager;
use GraphQL\Type\Definition\InputObjectType;

class ListInput extends InputObjectType
{
    const SM_NAME = 'ListInput';
    public function __construct(ServiceManager $serviceManager)
    {
        $config = [
            'name' => static::SM_NAME,
            'fields' => [
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
