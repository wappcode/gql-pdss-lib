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
                Graphql
                Services



Crear archivo modules/AppModule/src/Services/AppRouter.php con el siguiente contenido


    <?php

    namespace AppModule\Services;

    use GPDCore\Library\RouteModel;
    use GPDCore\Library\AbstractRouter;
    use GPDCore\Controllers\GraphqlController;

        class AppRouter extends AbstractRouter
        {


            protected function addRoutes()
            {
                $GraphqlMethod = $this->isProductionMode ? 'POST' : ['POST','GET'];

                // Agrega las entradas para consultas graphql 
                $this->addRoute(new RouteModel($GraphqlMethod, '/api', GraphqlController::class));

                // Las demás rutas deben ir abajo para poder utilizar la configuración de los módulos y sus servicios

                // entrada dominio principal

                // ... otras rutas
            }

        }

Agregar el archivo modules/AppModule/config/module.config.php

    <?php

    return [];

Agregar el archivo modules/AppModule/src/AppModule.php

    <?php

    namespace AppModule;

    use GPDCore\Library\AbstractModule;
    use GraphQL\Type\Definition\Type;

    class AppModule extends AbstractModule {

        /**
        * Array con la configuración del módulo
        *
        * @return array
        */
        function getConfig(): array {
            return require(__DIR__.'/../config/module.config.php');
        }
        function getServicesAndGQLTypes(): array
        {
            return [
                'invokables' => [],
                'factories'=> [],
                'aliases' => []
            ];
        }
        /**
        * Array con los resolvers del módulo
        *
        * @return array array(string $key => callable $resolver)
        */
        function getResolvers(): array {
            return [];
        }
        /**
        * Array con los graphql Queries del módulo
        *
        * @return array
        */
        function getQueryFields(): array {
            return [
                'echo' =>  [
                    'type' => Type::nonNull(Type::string()),
                    'args' => [
                        'message' => Type::nonNull(Type::string())
                    ],

                    'resolve' => function($root, $args) { return $args["message"];}
                ],
            ];
        }
        /**
        * Array con los graphql mutations del módulo
        *
        * @return array
        */
        function getMutationFields(): array {
            return [];
        }

    

    } 


Agregar al archivo composer.json el siguiente código

     "autoload": {
        "psr-4": {
            "AppModule\\": "modules/AppModule/src/"
        }
    }


Ejecutar ./composer.phar dump-autoload -o

Crear un archivo para sobreescribir la configuración de los módulos

```
config/local.config.php
```

```
<?php
return [];
```


Crear un archivo public/index.php con el siguiente contenido
``` 
<?php

use AppModule\Services\AppRouter;
use GPDApp\GPDAppModule;
use GPDCore\Library\GPDApp;
use GPDCore\Services\ContextService;
use Laminas\ServiceManager\ServiceManager;

require_once __DIR__ . "/../vendor/autoload.php";

$production = getenv("APP_ENV");
$serviceManager = new ServiceManager();
$context = new ContextService($serviceManager);
$router = new AppRouter();
$app = new GPDApp($context, $router, $enviroment);
$app->addModules([
    GPDAppModule::class,
]);
$localConfig = require __DIR__."/../config/local.config.php";
$context->getConfig()->add($localConfig);
$app->run();
````


Agregar archivo config/doctrine.local.php con el siguiente contenido

    <?php
    return [
        "driver"=> [
            'user'     =>   '',
            'password' =>   '',
            'dbname'   =>   '',
            'driver'   =>   'pdo_mysql',
            'host'   =>     '127.0.0.1',
            'charset' =>    'utf8mb4'
        ],
        "entities"=> []
    ];


Crear archivo cli-config.php con el siguiente código

    <?php

    use GPDCore\Factory\EntityManagerFactory;
    use Doctrine\ORM\Tools\Console\ConsoleRunner;

    require_once __DIR__."/vendor/autoload.php";
    $options = require __DIR__."/config/doctrine.local.php";
    $entityManager = EntityManagerFactory::createInstance($options, '', true, '');

    return ConsoleRunner::createHelperSet($entityManager);




## Crear entities en AppModule


Crear archivo modules/AppModule/src/Entities/Post.php

    <?php

    declare(strict_types=1);

    namespace GraphQLTests\Doctrine\Blog\Model;

    use DateTimeImmutable;
    use Doctrine\ORM\Mapping as ORM;
    use GPDCore\Entities\AbstractEntityModel;

    /**
    *
    * @ORM\Entity
    * 
    */
    final class Post extends AbstractEntityModel
    {
        const STATUS_PRIVATE = 'private';
        const STATUS_PUBLIC = 'public';

        /**
        * @var string
        *
        * @ORM\Column(type="string", length=50, options={"default" = ""})
        */
        private $title = '';

        /**
        * @var string
        *
        * @ORM\Column(type="text")
        */
        private $body = '';

        /**
        * @var DateTimeImmutable
        *
        * @ORM\Column(type="datetime_immutable")
        */
        private $publicationDate;

        /**
        * @var string
        *
        * @ORM\Column(type="string", options={"default" = Post::STATUS_PRIVATE})
        */
        private $status = self::STATUS_PRIVATE;


        /**
        *
        * @return  string
        */ 
        public function getTitle(): string
        {
            return $this->title;
        }

        /**
        *
        * @param  string  $title
        *
        * @return  self
        */ 
        public function setTitle(string $title)
        {
            $this->title = $title;

            return $this;
        }

        /**
        *
        * @return  string
        */ 
        public function getBody(): string
        {
            return $this->body;
        }

        /**
        *
        * @param  string  $body
        *
        * @return  self
        */ 
        public function setBody(string $body)
        {
            $this->body = $body;

            return $this;
        }

        /**
        *
        * @return  DateTimeImmutable
        */ 
        public function getPublicationDate(): DateTimeImmutable
        {
            return $this->publicationDate;
        }

        /**
        *
        * @param  DateTimeImmutable  $publicationDate
        *
        * @return  self
        */ 
        public function setPublicationDate(DateTimeImmutable $publicationDate)
        {
            $this->publicationDate = $publicationDate;

            return $this;
        }

        /**
        *
        * @return  string
        */ 
        public function getStatus()
        {
            return $this->status;
        }

        /**
        *
        * @param  string  $status
        *
        * @return  self
        */ 
        public function setStatus(string $status)
        {
            $this->status = $status;

            return $this;
        }
    }




Agregar al archivo config/doctrine.local.php la ubicación de las entidades

    "entities"=> [
            "AppModule\Entities" => __DIR__."/../modules/AppModule/src/Entities",
    ]


Ejecutar el siguiente comando para generar el código SQL para actualizar la base de datos

    ./vendor/bin/doctrine orm:schema-tool:update --dump-sql

O Ejecutar el siguiente comando para actualizar la base de datos

    ./vendor/bin/doctrine orm:schema-tool:update --force

NOTA: Estos comandos no deben ser utilizados en producción

Iniciar con el comándo

    php -S localhost:8000 public/index.php


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