<?php

namespace AppModule;

use AppModule\Entities\Account;
use AppModule\Entities\Comment;
use AppModule\Entities\Post;
use AppModule\Entities\User;
use AppModule\Graphql\ResolversAccount;
use AppModule\Graphql\ResolversComment;
use AppModule\Graphql\ResolversPost;
use AppModule\Graphql\ResolversUser;
use AppModule\Graphql\TypeAccountConnection;
use AppModule\Graphql\TypeCommentConnection;
use AppModule\Graphql\TypePostConnection;
use AppModule\Graphql\TypeUserConnection;
use DateTime;
use GPDCore\Graphql\GPDFieldResolveFactory;
use GPDCore\Graphql\Types\DateType;
use GPDCore\Library\AbstractModule;
use GPDCore\Library\IContextService;
use GPDCore\Library\ProxyUtilities;
use GraphQL\Type\Definition\Type;

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
        // $serviceManager = $this->context->getServiceManager();
        // $userConnectionType = $serviceManager->get(TypeUserConnection::NAME);
        // $postConnectionType = $serviceManager->get(TypePostConnection::NAME);
        // $accountConnectionType = $serviceManager->get(TypeAccountConnection::NAME);
        // $commentConnectionType = $serviceManager->get(TypeCommentConnection::NAME);
        // return [
        //     'echo' =>  [
        //         'type' => Type::nonNull(Type::string()),
        //         'args' => [
        //             'message' => Type::nonNull(Type::string())
        //         ],

        //         'resolve' => function ($root, $args) {
        //             return $args["message"];
        //         }
        //     ],
        //     'datetime' => [
        //         'type' => Type::nonNull($this->context->getServiceManager()->get(DateTime::class)),
        //         'resolve' => function ($root, $args) {
        //             return new DateTime();
        //         }
        //     ],
        //     'date' => [
        //         'type' => Type::nonNull($this->context->getServiceManager()->get(DateType::class)),
        //         'resolve' => function ($root, $args) {
        //             return new DateTime();
        //         }
        //     ],
        //     'userConnection' => GPDFieldResolveFactory::buildFieldConnection($this->context, $userConnectionType, User::class),
        //     'user' => GPDFieldResolveFactory::buildFieldItem($this->context, User::class),
        //     'postConnection' => GPDFieldResolveFactory::buildFieldConnection($this->context, $postConnectionType, Post::class),
        //     'post' => GPDFieldResolveFactory::buildFieldItem($this->context, Post::class),
        //     'accountConnection' => GPDFieldResolveFactory::buildFieldConnection($this->context, $accountConnectionType, Account::class),
        //     'account' => GPDFieldResolveFactory::buildFieldItem($this->context, Account::class),
        //     'commentConnection' => GPDFieldResolveFactory::buildFieldConnection($this->context, $commentConnectionType, Comment::class),
        //     'comment' => GPDFieldResolveFactory::buildFieldItem($this->context, Comment::class),

        // ];
    }

    /**
     * Array con los graphql mutations del módulo.
     */
    public function getMutationFields(): array
    {
        return [];
        // return [
        //     'createUser' => GPDFieldResolveFactory::buildFieldCreate($this->context, User::class),
        //     'updateUser' => GPDFieldResolveFactory::buildFieldUpdate($this->context, User::class),
        //     'deleteUser' => GPDFieldResolveFactory::buildFieldDelete($this->context, User::class),
        //     'createAccount' => GPDFieldResolveFactory::buildFieldCreate($this->context, Account::class),
        //     'updateAccount' => GPDFieldResolveFactory::buildFieldUpdate($this->context, Account::class),
        //     'deleteAccount' => GPDFieldResolveFactory::buildFieldDelete($this->context, Account::class),
        //     'createPost' => GPDFieldResolveFactory::buildFieldCreate($this->context, Post::class),
        //     'updatePost' => GPDFieldResolveFactory::buildFieldUpdate($this->context, Post::class),
        //     'deletePost' => GPDFieldResolveFactory::buildFieldDelete($this->context, Post::class),
        //     'createComment' => GPDFieldResolveFactory::buildFieldCreate($this->context, Comment::class),
        //     'updateComment' => GPDFieldResolveFactory::buildFieldUpdate($this->context, Comment::class),
        //     'deleteComment' => GPDFieldResolveFactory::buildFieldDelete($this->context, Comment::class),

        // ];
    }
}
