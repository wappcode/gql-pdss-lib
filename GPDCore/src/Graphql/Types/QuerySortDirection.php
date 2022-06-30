<?php 

declare(strict_types=1);

namespace GPDCore\Graphql\Types;

use GraphQL\Type\Definition\EnumType;
use PDSSUtilities\QuerySort;

class QuerySortDirection extends EnumType {
    const SM_NAME = "QuerySortDirection";
    public function __construct()
    {
        $config = [
            'name' => static::SM_NAME,
            'values' => [
               QuerySort::DIRECTION_ASC,
               QuerySort::DIRECTION_DESC,

            ],
        ];

        parent::__construct($config);
    }
}