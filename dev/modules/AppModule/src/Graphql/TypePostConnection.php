<?php

namespace AppModule\Graphql;

use GPDCore\Library\AbstractConnectionTypeServiceFactory;

class TypePostConnection extends AbstractConnectionTypeServiceFactory
{
    const NAME = 'PostConnection';
    const DESCRIPTION = '';
    protected static $instance = null;
}
