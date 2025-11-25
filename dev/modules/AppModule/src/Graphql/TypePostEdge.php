<?php

namespace AppModule\Graphql;

use GPDCore\Library\AbstractEdgeTypeServiceFactory;

class TypePostEdge extends AbstractEdgeTypeServiceFactory
{
    public const NAME = 'PostEdge';
    public const DESCRIPTION = '';

    protected static $instance;
}
