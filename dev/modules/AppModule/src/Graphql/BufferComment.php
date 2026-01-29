<?php

namespace AppModule\Graphql;

use AppModule\Entities\Comment;
use GPDCore\DataLoaders\EntityDataLoader;

class BufferComment
{
    private static ?EntityDataLoader $instance = null;

    public static function getInstance(): EntityDataLoader
    {
        if (static::$instance === null) {
            static::$instance = new EntityDataLoader(Comment::class);
        }

        return static::$instance;
    }
}
