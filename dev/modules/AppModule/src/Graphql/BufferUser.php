<?php

namespace AppModule\Graphql;

use AppModule\Entities\User;
use GPDCore\DataLoaders\EntityDataLoader;

class BufferUser
{
    private static ?EntityDataLoader $instance = null;

    public static function getInstance(): EntityDataLoader
    {
        if (static::$instance === null) {
            static::$instance = new EntityDataLoader(User::class);
        }

        return static::$instance;
    }
}
