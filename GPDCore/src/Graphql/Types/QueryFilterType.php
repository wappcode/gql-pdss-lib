<?php

declare(strict_types=1);

namespace GPDCore\Graphql\Types;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use Laminas\ServiceManager\ServiceManager;

class QueryFilterType extends InputObjectType
{
    public const SM_NAME = 'QueryFilterInput';

    public function __construct(ServiceManager $serviceManager)
    {
        $config = [
            'name' => static::SM_NAME,
            'fields' => [
                'groupLogic' => [
                    'type' => $serviceManager->get(QueryFilterLogic::SM_NAME),
                ],
                'conditionsLogic' => [
                    'type' => $serviceManager->get(QueryFilterLogic::SM_NAME),
                ],
                'conditions' => [
                    'type' => Type::listOf($serviceManager->get(QueryFilterConditionType::SM_NAME)),
                ],
                'compoundConditions' => [
                    'type' => Type::listOf($serviceManager->get(QueryFilterCompoundConditionInput::SM_NAME)),
                ],
            ],
        ];

        parent::__construct($config);
    }
}
