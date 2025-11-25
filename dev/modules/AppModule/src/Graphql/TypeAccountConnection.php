<?php

namespace AppModule\Graphql;

use GPDCore\Library\AbstractConnectionTypeServiceFactory;

class TypeAccountConnection extends AbstractConnectionTypeServiceFactory
{
    public const NAME = 'AccountConnection';
    public const DESCRIPTION = 'Account connection type';

    protected static $instance;
}
