<?php

namespace AppModule\Graphql;

use AppModule\Entities\Account;
use AppModule\Entities\User;
use GPDCore\Contracts\QueryModifierInterface;
use GPDCore\Graphql\ResolverFactory;

class ResolversAccount
{
    public static function getUsersResolver(?callable $proxy = null, ?QueryModifierInterface $queryDecorator = null): callable
    {
        $resolver = ResolverFactory::forCollection(Account::class, 'users', User::class, $queryDecorator);

        return is_callable($proxy) ? $proxy($resolver) : $resolver;
    }
}
