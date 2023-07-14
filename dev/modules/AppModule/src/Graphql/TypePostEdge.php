<?php

namespace AppModule\Graphql;

use GPDCore\Library\AbstractEdgeTypeServiceFactory;

class TypePostEdge extends AbstractEdgeTypeServiceFactory
{
    const NAME = 'PostEdge';
    const DESCRIPTION = '';
    protected static $instance = null;
}
