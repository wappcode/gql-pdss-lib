<?php

namespace AppModule\Graphql;

use GPDCore\Library\AbstractEdgeTypeServiceFactory;

class TypeUserEdge extends AbstractEdgeTypeServiceFactory
{
    const NAME = 'UserEdge';
    const DESCRIPTION = '';
    protected static $instance = null;
}
