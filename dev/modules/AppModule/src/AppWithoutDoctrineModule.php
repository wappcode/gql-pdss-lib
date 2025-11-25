<?php

namespace AppModule;

use GPDCore\Library\AbstractModule;
use GraphQL\Type\Definition\Type;

class AppWithoutDoctrineModule extends AbstractModule
{
    /**
     * Array con la configuración del módulo.
     */
    public function getConfig(): array
    {
        return [];
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
        return [];
    }

    /**
     * Array con los graphql Queries del módulo.
     */
    public function getQueryFields(): array
    {
        return [
            'echo' =>  [
                'type' => Type::nonNull(Type::string()),
                'args' => [
                    'message' => Type::nonNull(Type::string()),
                ],

                'resolve' => function ($root, $args) {
                    return $args['message'];
                },
            ],
        ];
    }

    /**
     * Array con los graphql mutations del módulo.
     */
    public function getMutationFields(): array
    {
        return [];
    }
}
