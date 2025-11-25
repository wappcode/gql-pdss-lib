<?php

namespace AppModule\Graphql;

use GPDCore\Library\AbstractEdgeTypeServiceFactory;

class TypeCommentEdge extends AbstractEdgeTypeServiceFactory
{
    public const NAME = 'CommentEdge';
    public const DESCRIPTION = '';

    protected static $instance;
}
