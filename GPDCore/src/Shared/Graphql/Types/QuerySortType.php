<?php

declare(strict_types=1);

namespace GPDCore\Shared\Graphql\Types;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use Laminas\ServiceManager\ServiceManager;

class QuerySortType extends InputObjectType
{
    public const SM_NAME = 'QuerySortInput';

    public function __construct(ServiceManager $serviceManager)
    {
        $config = [
            'name' => static::SM_NAME,
            'fields' => [
                'property' => [
                    'type' => Type::nonNull(Type::string()),
                ],
                'direction' => [
                    'type' => $serviceManager->get(QuerySortDirection::SM_NAME),
                ],
                'onJoinedProperty' => [
                    'type' => Type::string(),
                ],
            ],
        ];

        parent::__construct($config);
    }
}
