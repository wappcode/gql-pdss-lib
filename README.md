Intalar
-------


Se puede utilizar la APP Skeleton en el repositorio git

    git clone https://github.com/jesus-abarca-g/gql-pdss.git


## Usar Composer

Repositorio GIT privado se requiere permisos y claves ssh

Ejecutar

    ./composer.phar init

Agregar al archivo composer.json las referencias a la libreria

    {
    "name": "name/project",
    "type": "project",
    "require": {
        "codewebapp/gqlpdss": "dev-master"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:jesus-abarca-g/gql-pdss-lib.git"
        }
     ]
    }

Ejecutar

    ./composer.phar require



Crear estructura de directorios

    config
    data
    modules
    public

Crear módulo principal AppModule en la carpeta modules

Crear la siguiente estructura de directorios

    modules
        AppModule
            config
            src
                AppModule
                    Controllers
                    Graphql
                    Services



Crear archivo modules/AppModule/src/AppModule/Services/AppRouter.php con el siguiente contenido


    <?php

    namespace AppModule\Services;

    use GPDCore\Library\RouteModel;
    use GPDCore\Library\AbstractRouter;
    use GPDApp\Controller\IndexController;
    use GPDApp\Controller\GraphqlController;

    class AppRouter extends AbstractRouter
    {

        protected function addRoutes()
        {
            $GraphqlMethod = ['POST'];
      
            // Agrega las entradas para consultas graphql 
            $this->addRoute(new RouteModel($GraphqlMethod, '/api', GraphqlController::class));

            // Las demás rutas deben ir abajo para poder utilizar la configuración de los módulos y sus servicios

            // entrada dominio principal
            $this->addRoute(new RouteModel('GET', '/', IndexController::class));
        
            // ... otras rutas
        }

    }


Agregar al archivo composer.json el siguiente código

     "autoload": {
        "psr-0": {
            "AppModule\\": "modules/AppModule/src/"
        }
    }


Ejecutar ./composer.phar dump-auto-load -o
Crear un archivo public/index.php con el siguiente contenido





# API

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