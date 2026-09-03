<?php

declare(strict_types=1);

namespace GPDCore\Shared\Graphql\Types;

use GraphQL\Type\Definition\EnumType;

class QueryFilterConditionTypeValue extends EnumType
{
    public const SM_NAME = 'QueryFilterConditionTypeValue';

    public function __construct()
    {
        parent::__construct([
            'name' => static::SM_NAME,
            'values' => [
                'STRING' => ['value' => 'string'],
                'NUMBER' => ['value' => 'number'],
                'BOOLEAN' => ['value' => 'boolean'],
            ],
        ]);
    }
}
