<?php

namespace AppModule\Graphql;

use AppModule\Entities\Post;
use GPDCore\DataLoaders\EntityDataLoader;

class BufferPost
{
    private static ?EntityDataLoader $instance = null;

    public static function getInstance(): EntityDataLoader
    {
        if (static::$instance === null) {
            static::$instance = new EntityDataLoader(Post::class);
        }

        return static::$instance;
    }
}
