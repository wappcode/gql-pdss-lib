<?php

declare(strict_types=1);

namespace GPDCore\Graphql\Types;

use GraphQL\Type\Definition\Type;
use Laminas\ServiceManager\ServiceManager;
use GraphQL\Type\Definition\InputObjectType;
use GPDCore\Graphql\Types\QuerySortDirection;

class QuerySortType extends InputObjectType{

    public function __construct(ServiceManager $serviceManager)
    {
        $config = [
            'name' => 'QuerySortType',
            'fields' => [
                'property' => [
                    'type' => Type::nonNull(Type::string()),
                ],
                'direction' => [
                    'type' => $serviceManager->get(QuerySortDirection::class),
                ],
                'joinProperty' => [
                    'type' => Type::boolean(),
                    // 'description' => 'nombre de la propiedad que es una referencia de otro objeto y de la cual se va a realizar el filtro. es necesario agregar manualmente los joins'
                ],
            ]
        ];

        parent::__construct($config);
    }

}