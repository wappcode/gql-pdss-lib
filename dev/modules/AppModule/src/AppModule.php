<?php

namespace AppModule;

use AppModule\Entities\User;
use AppModule\Graphql\ResolversAccount;
use AppModule\Graphql\ResolversComment;
use AppModule\Graphql\ResolversPost;
use AppModule\Graphql\ResolversUser;
use DateTime;
use GPDCore\Graphql\GPDFieldResolveFactory;
use GPDCore\Library\AbstractModule;
use GPDCore\Library\IContextService;
use GPDCore\Library\ProxyUtilities;

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

    public function getServicesAndGQLTypes(): array
    {
        return [
            'invokables' => [],
            'factories' => [],
            'aliases' => [],
        ];
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
            'Query::showDate' => fn ($root, $args, IContextService $context, $info) =>  new DateTime(),
            'User::accounts' => ResolversUser::getAccountsResolver(),
            'User::posts' => ResolversUser::getPostsResolver(),
            'Account::users' => ResolversAccount::getUsersResolver(),
            'Post::author' => ResolversPost::getAuthorResolver(),
            'Post::comments' => ResolversPost::getCommentsResolver(),
            'Comment::post' => ResolversComment::getPostResolver(),
            'Query::echo' => $echoResolve,
            'Query::echoProxy' => ProxyUtilities::apply($echoResolve, $proxyEcho1),
            'Query::echoProxies' => ProxyUtilities::applyAll($echoResolve, [$proxyEcho1, $proxyEcho2]),
            'Query::getUsers' => GPDFieldResolveFactory::buildForConnection(User::class),
            'Query::getUser' => GPDFieldResolveFactory::buildForItem(User::class),
            'Mutation::createUser' => GPDFieldResolveFactory::buildforCreate(User::class),
            'Mutation::updateUser' => GPDFieldResolveFactory::buildForUpdate(User::class),
            'Mutation::deleteUser' => GPDFieldResolveFactory::buildForDelete(User::class),
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
