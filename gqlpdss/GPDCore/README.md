Instrucciones
-------


Agregar un archivo doctrine.local.php en el siguiente directorio ../../config tomando en considación la ruta del directorio donde se encuentra este archivo

## Clases



### ConnectionTypeFactory 

Clase que genera tipos connection para consultas de listas con paginación

#### Metodos

createConnectionType (\GraphQL\Type\Definition\ObjectType $type, string $name, string $description): \GraphQL\Type\Definition\ObjectType

Crea un tipo connection con los siguientes campos

    {
        totalCount: int!
        pageInfo: PageInfoType! {
            hasPreviousPage: bool!
            hasNextPage: bool!
            startCursor: string!
            endCursor: string!
        },
        edges: [EdgeType]! {
            cursor: string!,
            node: ObjectType!
        }
    }


getPageInfoType(): \GraphQL\Type\Definition\ObjectType

Crea un tipo PageInfo

    {
        hasPreviousPage: bool!
        hasNextPage: bool!
        startCursor: string!
        endCursor: string!
    }
createEdgeType(\GraphQL\Type\Definition\ObjectType $nodeType): \GraphQL\Type\Definition\ObjectType

Crea un tipo Edge

    {
        cursor: string!,
        node: ObjectType!
    }