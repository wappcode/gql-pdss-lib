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
            'factories' => [
                TypeUserEdge::NAME => TypeUserEdge::getFactory($this->context, User::class),
                TypeUserConnection::NAME =>  TypeUserConnection::getFactory($this->context, TypeUserEdge::NAME),
                TypePostEdge::NAME => TypePostEdge::getFactory($this->context, Post::class),
                TypePostConnection::NAME => TypePostConnection::getFactory($this->context, Post::class),
                TypeCommentEdge::NAME => TypeCommentEdge::getFactory($this->context, Comment::class),
                TypeCommentConnection::NAME => TypeCommentConnection::getFactory($this->context, Comment::class),
                TypeAccountEdge::NAME => TypeAccountEdge::getFactory($this->context, Account::class),
                TypeAccountConnection::NAME => TypeAccountConnection::getFactory($this->context, Account::class)
            ],
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
            'User::accounts' => ResolversUser::getAccountsResolver(),
            'User::posts' => ResolversUser::getPostsResolver(),
            'Account::users' => ResolversAccount::getUsersResolver(),
            'Post::author' => ResolversPost::getAuthorResolver(),
            'Post::comments' => ResolversPost::getCommentsResolver(),
            'Comment::post' => ResolversComment::getPostResolver()


        ];
    }
    /**
     * Array con los graphql Queries del módulo
     *
     * @return array
     */
    function getQueryFields(): array
    {

        return [
            'echo' =>  [
                'type' => Type::nonNull(Type::string()),
                'args' => [
                    'message' => Type::nonNull(Type::string())
                ],

                'resolve' => function ($root, $args) {
                    return $args["message"];
                }
            ],
            'datetime' => [
                'type' => Type::nonNull($this->context->getServiceManager()->get(DateTime::class)),
                'resolve' => function ($root, $args) {
                    return new DateTime();
                }
            ],
            'date' => [
                'type' => Type::nonNull($this->context->getServiceManager()->get(DateType::class)),
                'resolve' => function ($root, $args) {
                    return new DateTime();
                }
            ]
        ];
    }
    /**
     * Array con los graphql mutations del módulo
     *
     * @return array
     */
    function getMutationFields(): array
    {
        return [];
    }
}
