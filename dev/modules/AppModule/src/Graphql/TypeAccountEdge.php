<?php

namespace AppModule\Graphql;

use GPDCore\Library\AbstractEdgeTypeServiceFactory;

class TypeAccountEdge extends AbstractEdgeTypeServiceFactory
{

    const NAME = "AccountEdge";
    const DESCRIPTION = "Account edge type";
    protected static $instance  = null;
}
