<?php

namespace AppModule\Graphql;

use AppModule\Entities\Account;
use AppModule\Entities\User;
use GPDCore\Contracts\QueryModifierInterface;
use GPDCore\Graphql\ResolverFactory;

class ResolversUser
{
    public static function getAccountsResolver(?callable $proxy = null, ?QueryModifierInterface $queryDecorator = null): callable
    {
        $resolver = ResolverFactory::createCollectionResolver(User::class, 'accounts', Account::class, $queryDecorator);

        return is_callable($proxy) ? $proxy($resolver) : $resolver;
    }

    public static function getPostsResolver(?callable $proxy = null, ?QueryModifierInterface $queryDecorator = null): callable
    {
        $resolver = ResolverFactory::createCollectionResolver(User::class, 'users', User::class, $queryDecorator);

        return is_callable($proxy) ? $proxy($resolver) : $resolver;
    }
}
