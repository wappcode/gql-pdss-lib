<?php

namespace AppModule\Graphql;

use AppModule\Entities\Comment;
use AppModule\Entities\Post;
use GPDCore\Library\QueryDecorator;
use GPDCore\Library\ResolverFactory;

class ResolversPost
{
    public static function getAuthorResolver(?callable $proxy = null): callable
    {
        $buffer = BufferUser::getInstance();

        $resolver = ResolverFactory::createEntityResolver($buffer, 'author');
        return is_callable($proxy) ? $proxy($resolver) : $resolver;
    }

    public static function getCommentsResolver(?callable $proxy = null, QueryDecorator $queryDecorator = null): callable
    {
        $resolver = ResolverFactory::createCollectionResolver(Post::class, 'comments', [], Comment::class, $queryDecorator);
        return is_callable($proxy) ? $proxy($resolver) : $resolver;
    }
}
