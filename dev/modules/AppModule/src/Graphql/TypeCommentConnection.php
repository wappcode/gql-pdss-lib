<?php

namespace AppModule\Graphql;

use GPDCore\Library\AbstractConnectionTypeServiceFactory;

class TypeCommentConnection extends AbstractConnectionTypeServiceFactory
{
    const NAME = "CommentConnection";
    const DESCRIPTION = "";
    protected static $instance = null;
}
