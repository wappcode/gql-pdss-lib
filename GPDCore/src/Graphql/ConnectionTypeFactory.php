<?php

declare(strict_types=1);

namespace GPDCore\Graphql;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;
use GPDCore\Library\IContextService;
use GraphQL\Type\Definition\InputObjectType;


class ConnectionTypeFactory
{



    private static $paginationInput;
    private static $pageInfoType;


    /**
     * Crea un tipo graphql para paginación de listas
     * @param $name El nombre que se va a utilizar para generar el nombre del tipo
     * @param $description La descripción que se va a mostrar en la documentación
     * @return \GraphQL\Type\Definition\ObjectType
     */
    public static function createConnectionType(IContextService $context, ObjectType $edgeType, string $name, string $description): ObjectType
    {
        $serviceManager = $context->getServiceManager();
        return new ObjectType([
            'name' => $name,
            'description' => $description,
            'fields' => [
                'totalCount' => Type::nonNull(Type::int()),
                'pageInfo' => $serviceManager->get('PageInfo'),
                'edges' => Type::nonNull(Type::listOf($edgeType))
            ]
        ]);
    }

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
                    'endCursor' =>  Type::nonNull(Type::string())
                ]
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
            ]
        ]);
    }

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

                ]
            ]);
        }
        return static::$paginationInput;
    }
}
