<?php

declare(strict_types=1);

namespace GPDCore\Graphql\Types;

use GraphQL\Type\Definition\Type;
use Laminas\ServiceManager\ServiceManager;
use GPDCore\Graphql\Types\QueryFilterLogic;
use GraphQL\Type\Definition\InputObjectType;
use GPDCore\Graphql\Types\QueryFilterConditionType;

class QueryFilterType extends InputObjectType{

    public function __construct(ServiceManager $serviceManager)
    {
        $config = [
            'name' => 'QueryFilterType',
            'fields' => [
                'groupLogic' => [
                    'type' => $serviceManager->get(QueryFilterLogic::class),
                ],
                'conditionsLogic' => [
                    'type' => $serviceManager->get(QueryFilterLogic::class),
                ],
                'conditions' => [
                    'type' => Type::nonNull(Type::listOf($serviceManager->get(QueryFilterConditionType::class))),
                ],
            ]
        ];

        parent::__construct($config);
    }

}