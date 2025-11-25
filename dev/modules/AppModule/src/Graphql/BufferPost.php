<?php

namespace AppModule\Graphql;

use AppModule\Entities\Post;
use GPDCore\Library\EntityBuffer;

class BufferPost
{
    private static $instance;

    public static function getInstance(): EntityBuffer
    {
        if (static::$instance === null) {
            static::$instance = new EntityBuffer(Post::class);
        }

        return static::$instance;
    }
}
