<?php

namespace AppModule;

use AppModule\Entities\User;
use AppModule\Graphql\ResolversAccount;
use AppModule\Graphql\ResolversComment;
use AppModule\Graphql\ResolversPost;
use AppModule\Graphql\ResolversUser;
use DateTime;
use GPDCore\Contracts\AppContextInterface;
use GPDCore\Core\AbstractModule;
use GPDCore\Graphql\ResolverFactory;
use GPDCore\Graphql\ResolverMiddleware;

class AppModule extends AbstractModule
{
    /**
     * Array con la configuración del módulo.
     */
    public function getConfig(): array
    {
        return require __DIR__ . '/../config/module.config.php';
    }

    public function getSchema(): string
    {
        $schema = file_get_contents(__DIR__ . '/../config/schema.graphql');

        return $schema == false ? '' : $schema;
    }

    public function getServices(): array
    {
        return [
            'invokables' => [],
            'factories' => [],
            'aliases' => [],
        ];
    }

    public function getTypes(): array
    {
        return [];
    }

    public function getMiddlewares(): array
    {
        return [];
    }

    /**
     * Array con los resolvers del módulo.
     *
     * @return array array(string $key => callable $resolver)
     */
    public function getResolvers(): array
    {
        $proxyEcho1 = fn ($resolver) => fn ($root, $args, $context, $info) => 'Proxy 1 ' . $resolver($root, $args, $context, $info);
        $proxyEcho2 = fn ($resolver) => fn ($root, $args, $context, $info) => 'Proxy 2 ' . $resolver($root, $args, $context, $info);
        $echoResolve = fn ($root, $args, $context, $info) => $args['msg'];

        return [
            'Query::showDate' => fn ($root, $args, AppContextInterface $context, $info) =>  new DateTime(),
            'User::accounts' => ResolversUser::getAccountsResolver(),
            'User::posts' => ResolversUser::getPostsResolver(),
            'Account::users' => ResolversAccount::getUsersResolver(),
            'Post::author' => ResolversPost::getAuthorResolver(),
            'Post::comments' => ResolversPost::getCommentsResolver(),
            'Comment::post' => ResolversComment::getPostResolver(),
            'Query::echo' => $echoResolve,
            'Query::echoProxy' => ResolverMiddleware::wrap($echoResolve, $proxyEcho1),
            'Query::echoProxies' => ResolverMiddleware::chain($echoResolve, [$proxyEcho1, $proxyEcho2]),
            'Query::getUsers' => ResolverFactory::forConnection(User::class),
            'Query::getUser' => ResolverFactory::forItem(User::class),
            'Mutation::createUser' => ResolverFactory::forCreate(User::class),
            'Mutation::updateUser' => ResolverFactory::forUpdate(User::class),
            'Mutation::deleteUser' => ResolverFactory::forDelete(User::class),
        ];
    }

    /**
     * Array con los graphql Queries del módulo.
     */
    public function getQueryFields(): array
    {
        return [];
    }

    /**
     * Array con los graphql mutations del módulo.
     */
    public function getMutationFields(): array
    {
        return [];
    }
}
