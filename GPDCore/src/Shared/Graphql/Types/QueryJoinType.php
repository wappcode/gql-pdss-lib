<?php

declare(strict_types=1);

namespace GPDCore\Shared\Graphql\Types;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use Laminas\ServiceManager\ServiceManager;

class QueryJoinType extends InputObjectType
{
    public const SM_NAME = 'QueryJoinInput';

    public function __construct(ServiceManager $serviceManager)
    {
        $config = [
            'name' => static::SM_NAME,
            'fields' => [
                'property' => [
                    'type' => Type::nonNull(Type::string()),
                ],
                'joinType' => [
                    'type' => $serviceManager->get(QueryJoinTypeValue::SM_NAME),
                ],
                'alias' => [
                    'type' => Type::string(),
                ],
                'joinedProperty' => [
                    'type' => Type::string(),
                ],
            ],
        ];

        parent::__construct($config);
    }
}
