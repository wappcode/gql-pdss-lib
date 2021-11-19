<?php 

declare(strict_types=1);

namespace GPDCore\Graphql\Types;

use GPDCore\Library\QueryFilter;
use GraphQL\Type\Definition\EnumType;

class QueryFilterLogic extends EnumType {
    public function __construct()
    {
        $config = [
            'name' => 'QueryFilterLogic',
            'values' => [
                QueryFilter::LOGIC_AND,
                QueryFilter::LOGIC_OR,

            ],
        ];

        parent::__construct($config);
    }
}