<?php

declare(strict_types=1);

namespace GPDCore\Graphql\Types;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use Laminas\ServiceManager\ServiceManager;

class QueryFilterConditionType extends InputObjectType
{
    public const SM_NAME = 'QueryFilterConditionInput';

    public function __construct(ServiceManager $serviceManager)
    {
        $config = [
            'name' => static::SM_NAME,
            'fields' => [
                'filterOperator' => [
                    'type' => Type::nonNull($serviceManager->get(QueryFilterConditionTypeValue::SM_NAME)),
                ],
                'value' => [
                    'type' => Type::nonNull($serviceManager->get(QueryFilterConditionValueType::SM_NAME)),
                ],
                'property' => [
                    'type' => Type::nonNull(Type::string()),
                ],
                'onJoinedProperty' => [
                    'type' => Type::string(),
                ],
            ],
        ];

        parent::__construct($config);
    }
}
