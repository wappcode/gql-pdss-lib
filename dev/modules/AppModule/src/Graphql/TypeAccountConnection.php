<?php

namespace AppModule\Graphql;

use GPDCore\Library\AbstractConnectionTypeServiceFactory;

class TypeAccountConnection extends AbstractConnectionTypeServiceFactory
{

    const NAME = "AccountConnection";
    const DESCRIPTION = "Account connection type";
    protected static $instance  = null;
}
