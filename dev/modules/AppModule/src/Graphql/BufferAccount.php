<?php

namespace AppModule\Graphql;

use AppModule\Entities\Account;
use GPDCore\Library\EntityBuffer;

class BufferAccount
{
    private static $instance;

    public static function getInstance(): EntityBuffer
    {
        if (static::$instance === null) {
            static::$instance = new EntityBuffer(Account::class);
        }

        return static::$instance;
    }
}
