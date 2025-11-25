<?php

namespace AppModule\Graphql;

use AppModule\Entities\Comment;
use GPDCore\Library\EntityBuffer;

class BufferComment
{
    private static $instance;

    public static function getInstance(): EntityBuffer
    {
        if (static::$instance === null) {
            static::$instance = new EntityBuffer(Comment::class);
        }

        return static::$instance;
    }
}
