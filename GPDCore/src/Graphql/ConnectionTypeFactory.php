<?php

declare(strict_types=1);

namespace GPDCore\Graphql;

use GPDCore\Contracts\AppContextInterface;
use GPDCore\Graphql\Types\PageInfoType;
use GPDCore\Graphql\Types\PaginationInput;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class ConnectionTypeFactory
{
    private static $paginationInput;

    private static $pageInfoType;

    /**
     * Crea un tipo graphql para paginación de listas.
     *
     * @param $name        El nombre que se va a utilizar para generar el nombre del tipo
     * @param $description La descripción que se va a mostrar en la documentación
     */
    public static function createConnectionType(AppContextInterface $context, ObjectType $edgeType, string $name, string $description): ObjectType
    {
        $serviceManager = $context->getServiceManager();

        return new ObjectType([
            'name' => $name,
            'description' => $description,
            'fields' => [
                'totalCount' => Type::nonNull(Type::int()),
                'pageInfo' => $serviceManager->get(PageInfoType::SM_NAME),
                'edges' => Type::nonNull(Type::listOf($edgeType)),
            ],
        ]);
    }

    /**
     * @deprecated 2.1.4 Utilizar PageInfoType class
     */
    public static function getPageInfoType(): ObjectType
    {
        if (static::$pageInfoType === null) {
            static::$pageInfoType = new ObjectType([
                'name' => 'PageInfo',
                'description' => 'Información para paginación',
                'fields' => [
                    'hasPreviousPage' => Type::nonNull(Type::boolean()),
                    'hasNextPage' =>   Type::nonNull(Type::boolean()),
                    'startCursor' =>  Type::nonNull(Type::string()),
                    'endCursor' =>  Type::nonNull(Type::string()),
                ],
            ]);
        }

        return static::$pageInfoType;
    }

    public static function createEdgeType(ObjectType $nodeType, $name, $description = ''): ObjectType
    {
        return new ObjectType([
            'name' => $name,
            'description' => $description,
            'fields' => [
                'cursor' => Type::nonNull(Type::string()),
                'node' =>   Type::nonNull($nodeType),
            ],
        ]);
    }

    /**
     *  @deprecated 2.1.4 Utilizar PaginationInput class
     */
    public static function getPaginationInput()
    {
        if (static::$paginationInput === null) {
            static::$paginationInput = new InputObjectType([
                'name' => 'PaginationInput',
                'description' => 'Para obtener los siguientes elementos de la lista utilizar first,after y para obtener los elementos previos utilizar last, before',
                'fields' => [
                    'first' => Type::int(),
                    'after' => Type::string(),
                    'last' => Type::int(),
                    'before' => Type::string(),
                ],
            ]);
        }

        return static::$paginationInput;
    }
}
