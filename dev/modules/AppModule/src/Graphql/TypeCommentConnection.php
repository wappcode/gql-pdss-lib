<?php

namespace AppModule\Graphql;

use GPDCore\Library\AbstractConnectionTypeServiceFactory;

class TypeCommentConnection extends AbstractConnectionTypeServiceFactory
{
    public const NAME = 'CommentConnection';
    public const DESCRIPTION = '';

    protected static $instance;
}
