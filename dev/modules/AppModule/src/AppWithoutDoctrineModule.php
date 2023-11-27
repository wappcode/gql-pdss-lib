<?php

namespace AppModule;

use GraphQL\Type\Definition\Type;
use GPDCore\Library\AbstractModule;


class AppWithoutDoctrineModule extends AbstractModule
{

    /**
     * Array con la configuración del módulo
     *
     * @return array
     */
    function getConfig(): array
    {
        return [];
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
        return [];
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
