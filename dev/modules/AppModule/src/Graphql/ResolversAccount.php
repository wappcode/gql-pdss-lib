<?php

namespace AppModule\Graphql;

use AppModule\Entities\User;
use AppModule\Entities\Account;
use GPDCore\Library\QueryDecorator;
use GPDCore\Library\ResolverFactory;

class ResolversAccount
{
    public static function getUsersResolver(?callable $proxy = null, QueryDecorator $queryDecorator = null): callable
    {
        $resolver = ResolverFactory::createCollectionResolver(Account::class, 'users', [], User::class, $queryDecorator);
        return is_callable($proxy) ? $proxy($resolver) : $resolver;
    }
}
