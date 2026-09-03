<?php

namespace AppModule\Graphql;

use GPDCore\Application\Graphql\ResolverFactory;

class ResolversComment
{
    public static function getPostResolver(?callable $proxy = null): callable
    {
        $buffer = BufferPost::getInstance();

        $resolver = ResolverFactory::forEntity($buffer, 'post');

        return is_callable($proxy) ? $proxy($resolver) : $resolver;
    }
}
