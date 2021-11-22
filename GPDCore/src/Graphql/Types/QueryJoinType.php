<?php

declare(strict_types=1);

namespace GPDCore\Graphql\Types;

use GraphQL\Type\Definition\Type;
use Laminas\ServiceManager\ServiceManager;
use GraphQL\Type\Definition\InputObjectType;
use GPDCore\Graphql\Types\QueryJoinTypeValue;

class QueryJoinType extends InputObjectType{

   
    public function __construct(ServiceManager $serviceManager)
    {
        $config = [
            'name' => 'QueryJoin',
            'fields' => [
                'joinProperty' => [
                    'type' => Type::nonNull(Type::string()),
                ],
                'type' => [
                    'type' => $serviceManager->get(QueryJoinTypeValue::class),
                ],
                'alias' => [
                    'type' => Type::string(),
                ],
                'joinedAlias' => [
                    'type' => Type::string(),
                    // 'description' => 'nombre de la propiedad que es una referencia de otro objeto y de la cual se va a realizar el filtro. es necesario agregar manualmente los joins'
                ],
            ]
        ];

        parent::__construct($config);
    }

}