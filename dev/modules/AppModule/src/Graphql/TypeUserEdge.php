<?php

namespace AppModule\Graphql;

use GPDCore\Library\AbstractEdgeTypeServiceFactory;

class TypeUserEdge extends AbstractEdgeTypeServiceFactory
{
    public const NAME = 'UserEdge';
    public const DESCRIPTION = '';

    protected static $instance;
}
