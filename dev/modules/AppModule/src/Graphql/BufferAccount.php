<?php

namespace AppModule\Graphql;

use AppModule\Entities\Account;
use GPDCore\DataLoaders\EntityDataLoader;

class BufferAccount
{
    private static ?EntityDataLoader $instance = null;

    public static function getInstance(): EntityDataLoader
    {
        if (static::$instance === null) {
            static::$instance = new EntityDataLoader(Account::class);
        }

        return static::$instance;
    }
}
