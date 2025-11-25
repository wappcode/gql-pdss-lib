<?php

namespace AppModule\Graphql;

use GPDCore\Library\AbstractEdgeTypeServiceFactory;

class TypeAccountEdge extends AbstractEdgeTypeServiceFactory
{
    public const NAME = 'AccountEdge';
    public const DESCRIPTION = 'Account edge type';

    protected static $instance;
}
