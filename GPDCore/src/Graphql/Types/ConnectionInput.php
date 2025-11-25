<?php

declare(strict_types=1);

namespace GPDCore\Graphql\Types;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use Laminas\ServiceManager\ServiceManager;

class ConnectionInput extends InputObjectType
{
    public const SM_NAME = 'ConnectionInput';

    public function __construct(ServiceManager $serviceManager)
    {
        $config = [
            'name' => static::SM_NAME,
            'fields' => [
                'pagination' => [
                    'type' => $serviceManager->get(PaginationInput::SM_NAME),
                ],
                'filters' => [
                    'type' => Type::listOf($serviceManager->get(QueryFilterType::SM_NAME)),
                ],
                'sorts' => [
                    'type' => Type::listOf($serviceManager->get(QuerySortType::SM_NAME)),
                ],
                'joins' => [
                    'type' => Type::listOf($serviceManager->get(QueryJoinType::SM_NAME)),
                ],
            ],
        ];
        parent::__construct($config);
    }
}
