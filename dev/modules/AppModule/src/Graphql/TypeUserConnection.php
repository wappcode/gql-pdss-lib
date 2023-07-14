<?php

namespace AppModule\Graphql;

use GPDCore\Library\AbstractConnectionTypeServiceFactory;

class TypeUserConnection extends AbstractConnectionTypeServiceFactory
{
    const NAME = 'UserConnection';
    const DESCRIPTION = '';
    protected static $instance = null;
}
