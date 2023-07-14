<?php

namespace AppModule\Graphql;

use AppModule\Graphql\BufferPost;
use GPDCore\Library\ResolverFactory;

class ResolversComment
{
    public static function getPostResolver(?callable $proxy = null): callable
    {
        $buffer = BufferPost::getInstance();

        $resolver = ResolverFactory::createEntityResolver($buffer, 'post');
        return is_callable($proxy) ? $proxy($resolver) : $resolver;
    }
}
