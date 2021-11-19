<?php 

declare(strict_types=1);

namespace GPDCore\Graphql\Types;

use GPDCore\Library\QuerySort;
use GraphQL\Type\Definition\EnumType;

class QuerySortDirection extends EnumType {
    
    public function __construct()
    {
        $config = [
            'name' => 'QuerySortDirection',
            'values' => [
               QuerySort::DIRECTION_ASC,
               QuerySort::DIRECTION_DESC,

            ],
        ];

        parent::__construct($config);
    }
}