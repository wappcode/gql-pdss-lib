<?php

declare(strict_types=1);

namespace GPDCore\Graphql\Types;

use GPDCore\Graphql\Types\QueryFilterConditionTypeValue;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use Laminas\ServiceManager\ServiceManager;

class QueryFilterConditionType extends InputObjectType{

    public function __construct(ServiceManager $serviceManager)
    {
        $config = [
            'name' => 'QueryFilterCondition',
            'fields' => [
                'type' => [
                    'type' => Type::nonNull($serviceManager->get(QueryFilterConditionTypeValue::class)),
                ],
                'value' => [
                    'type' => Type::nonNull(Type::string()),
                ],
                'values' => [
                    'type' => Type::nonNull(Type::listOf(Type::string())),
                ],
                'property' => [
                    'type' => Type::nonNull(Type::string()),
                ],
                'not' => [
                    'type' => Type::boolean(),
                ],
                'joinProperty' => [
                    'type' => Type::string(),
                    // 'description' => 'nombre de la propiedad que es una referencia de otro objeto y de la cual se va a realizar el filtro. es necesario agregar manualmente los joins'
                ],
            ]
        ];

        parent::__construct($config);
    }

}