<?php

namespace AppModule\Graphql;

use AppModule\Entities\User;
use GPDCore\Library\EntityBuffer;

class BufferUser
{
    private static $instance;

    public static function getInstance(): EntityBuffer
    {
        if (static::$instance === null) {
            static::$instance = new EntityBuffer(User::class);
        }

        return static::$instance;
    }
}
