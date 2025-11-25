<?php

namespace AppModule\Graphql;

use GPDCore\Library\AbstractConnectionTypeServiceFactory;

class TypePostConnection extends AbstractConnectionTypeServiceFactory
{
    public const NAME = 'PostConnection';
    public const DESCRIPTION = '';

    protected static $instance;
}
