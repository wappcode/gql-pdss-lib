<?php

namespace AppModule;

use DateTime;
use GPDCore\Graphql\Types\DateType;
use GPDCore\Library\AbstractModule;
use GraphQL\Type\Definition\Type;

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
