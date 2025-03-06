<?php

namespace AppModule;

use AppModule\Entities\Account;
use DateTime;
use AppModule\Entities\Post;
use AppModule\Entities\User;
use AppModule\Entities\Comment;
use AppModule\Graphql\ResolversAccount;
use AppModule\Graphql\ResolversComment;
use AppModule\Graphql\ResolversPost;
use AppModule\Graphql\ResolversUser;
use AppModule\Graphql\TypeAccountConnection;
use AppModule\Graphql\TypeAccountEdge;
use AppModule\Graphql\TypeCommentConnection;
use GraphQL\Type\Definition\Type;
use AppModule\Graphql\TypePostEdge;
use AppModule\Graphql\TypeUserEdge;
use GPDCore\Graphql\Types\DateType;
use GPDCore\Library\AbstractModule;
use AppModule\Graphql\TypeCommentEdge;
use AppModule\Graphql\TypePostConnection;
use AppModule\Graphql\TypeUserConnection;
use GPDCore\Graphql\GPDFieldFactory;
use GPDCore\Library\IContextService;

class AppModule extends AbstractModule
{

    /**
     * Array con la configuración del módulo
     *
     * @return array
     */
    function getConfig(): array
    {
        return require(__DIR__ . '/../config/module.config.php');
    }
    function getServicesAndGQLTypes(): array
    {
        return [
            'invokables' => [],
            'factories' => [],
            'aliases' => []
        ];
    }
    /**
     * Array con los resolvers del módulo
     *
     * @return array array(string $key => callable $resolver)
     */
    function getResolvers(): array
    {
        return [
            'Query::greetings' => function ($root, $args, IContextService $context, $info) {
                $firstname = $args["input"]["firstName"];
                $lastname = $args["input"]["lastName"];
                return "Hello {$firstname} {$lastname}!";
            },
            'Query::showDate' => function ($root, $args, IContextService $context, $info) {
                return new DateTime();
            }

            // 'User::accounts' => ResolversUser::getAccountsResolver(),
            // 'User::posts' => ResolversUser::getPostsResolver(),
            // 'Account::users' => ResolversAccount::getUsersResolver(),
            // 'Post::author' => ResolversPost::getAuthorResolver(),
            // 'Post::comments' => ResolversPost::getCommentsResolver(),
            // 'Comment::post' => ResolversComment::getPostResolver()


        ];
    }
    /**
     * Array con los graphql Queries del módulo
     *
     * @return array
     */
    function getQueryFields(): array
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
        //     'userConnection' => GPDFieldFactory::buildFieldConnection($this->context, $userConnectionType, User::class),
        //     'user' => GPDFieldFactory::buildFieldItem($this->context, User::class),
        //     'postConnection' => GPDFieldFactory::buildFieldConnection($this->context, $postConnectionType, Post::class),
        //     'post' => GPDFieldFactory::buildFieldItem($this->context, Post::class),
        //     'accountConnection' => GPDFieldFactory::buildFieldConnection($this->context, $accountConnectionType, Account::class),
        //     'account' => GPDFieldFactory::buildFieldItem($this->context, Account::class),
        //     'commentConnection' => GPDFieldFactory::buildFieldConnection($this->context, $commentConnectionType, Comment::class),
        //     'comment' => GPDFieldFactory::buildFieldItem($this->context, Comment::class),

        // ];
    }
    /**
     * Array con los graphql mutations del módulo
     *
     * @return array
     */
    function getMutationFields(): array
    {
        return [];
        // return [
        //     'createUser' => GPDFieldFactory::buildFieldCreate($this->context, User::class),
        //     'updateUser' => GPDFieldFactory::buildFieldUpdate($this->context, User::class),
        //     'deleteUser' => GPDFieldFactory::buildFieldDelete($this->context, User::class),
        //     'createAccount' => GPDFieldFactory::buildFieldCreate($this->context, Account::class),
        //     'updateAccount' => GPDFieldFactory::buildFieldUpdate($this->context, Account::class),
        //     'deleteAccount' => GPDFieldFactory::buildFieldDelete($this->context, Account::class),
        //     'createPost' => GPDFieldFactory::buildFieldCreate($this->context, Post::class),
        //     'updatePost' => GPDFieldFactory::buildFieldUpdate($this->context, Post::class),
        //     'deletePost' => GPDFieldFactory::buildFieldDelete($this->context, Post::class),
        //     'createComment' => GPDFieldFactory::buildFieldCreate($this->context, Comment::class),
        //     'updateComment' => GPDFieldFactory::buildFieldUpdate($this->context, Comment::class),
        //     'deleteComment' => GPDFieldFactory::buildFieldDelete($this->context, Comment::class),

        // ];
    }
}
