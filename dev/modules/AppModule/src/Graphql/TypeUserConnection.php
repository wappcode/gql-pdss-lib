<?php

namespace AppModule\Graphql;

use GPDCore\Library\AbstractConnectionTypeServiceFactory;

class TypeUserConnection extends AbstractConnectionTypeServiceFactory
{
    public const NAME = 'UserConnection';
    public const DESCRIPTION = '';

    protected static $instance;
}
