# GQLPDSS-lib

Ingresa al siguiente link para más información. [Quick Start](https://wappcode.github.io/gql-pdss-lib-docs?1)

## Intalar

## Usar Composer

Ejecutar

    ./composer.phar init

Agregar al archivo composer.json las referencias a la libreria

    {
    "name": "name/project",
    "type": "project",
    "require": {
        "wappcode/gqlpdss": "^2.0.0"
    },
    }

Ejecutar

    ./composer.phar install

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
                module.config.php
                schema.graphql (opcional)
            src
                AppModule.php
                Entities
                Graphql
                    Resolvers*.php (opcional, para organizar resolvers)
                Services

Crear archivo modules/AppModule/config/module.config.php

```php
<?php
return [
    // Configuración específica del módulo
];
```

Agregar el archivo modules/AppModule/src/AppModule.php

```php
<?php

namespace AppModule;

use GPDCore\Core\AbstractModule;
use GPDCore\Graphql\ResolverFactory;
use GPDCore\Contracts\AppContextInterface;

class AppModule extends AbstractModule
{
    /**
     * Array con la configuración del módulo.
     */
    public function getConfig(): array
    {
        return require __DIR__ . '/../config/module.config.php';
    }

    /**
     * Schema GraphQL del módulo 
     */
    public function getSchema(): string
    {
        $schema = @file_get_contents(__DIR__ . '/../config/schema.graphql');
        return $schema ?: '';
    }

    /**
     * Servicios del módulo para ServiceManager.
     */
    public function getServices(): array
    {
        return [
            'invokables' => [],
            'factories' => [],
            'aliases' => []
        ];
    }

    /**
     * Tipos GraphQL personalizados del módulo.
     */
    public function getTypes(): array
    {
        return [];
    }

    /**
     * Middlewares HTTP del módulo.
     */
    public function getMiddlewares(): array
    {
        return [];
    }

    /**
     * Resolvers GraphQL del módulo.
     * 
     * @return array array(string $key => callable $resolver)
     */
    public function getResolvers(): array
    {
        return [
            // Ejemplo de resolver simple
            'Query::echo' => fn($root, $args, AppContextInterface $context, $info) => $args['message'],
            
            // Ejemplo usando ResolverFactory para CRUD
            // 'Query::getUsers' => ResolverFactory::forConnection(User::class),
            // 'Query::getUser' => ResolverFactory::forItem(User::class),
            // 'Mutation::createUser' => ResolverFactory::forCreate(User::class),
            // 'Mutation::updateUser' => ResolverFactory::forUpdate(User::class),
            // 'Mutation::deleteUser' => ResolverFactory::forDelete(User::class),
        ];
    }

    /**
     * Campos Query definidos programáticamente (opcional).
     * Nota: Preferible usar schema.graphql + getResolvers()
     */
    public function getQueryFields(): array
    {
        return [];
    }

    /**
     * Campos Mutation definidos programáticamente (opcional).
     * Nota: Preferible usar schema.graphql + getResolvers()
     */
    public function getMutationFields(): array
    {
        return [];
    }
}
```

Agregar al archivo composer.json el siguiente código

     "autoload": {
        "psr-4": {
            "AppModule\\": "modules/AppModule/src/"
        }
    }

Ejecutar ./composer.phar dump-autoload -o

Crear un archivo de configuración maestro

```
config/master.config.php
```

```php
<?php
return [
    // Configuración general de la aplicación
];
```

Crear un archivo para configuración local (no versionado)

```
config/local.config.php (opcional)
```

```php
<?php
return [
    // Configuración local que sobreescribe master.config.php
];
```

Crear un archivo public/index.php con el siguiente contenido

```php
<?php

use AppModule\AppModule;
use GPDCore\Factory\EntityManagerFactory;
use GPDCore\Core\AppConfig;
use GPDCore\Core\Application;
use GPDCore\Contracts\AppContextInterface;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\ServiceManager\ServiceManager;

require_once __DIR__ . '/../vendor/autoload.php';

// Configuración
$configFile = __DIR__ . '/../config/doctrine.local.php';
$cacheDir = __DIR__ . '/../data/DoctrineORMModule';
$enviroment = getenv('APP_ENV') ?: 'development';
$masterConfig = require __DIR__ . '/../config/master.config.php';

// Inicializar ServiceManager y Config
$serviceManager = new ServiceManager();
$config = AppConfig::getInstance()->setMasterConfig($masterConfig);

// Crear EntityManager
$options = file_exists($configFile) ? require $configFile : [];
$isDevMode = $enviroment !== AppContextInterface::ENV_PRODUCTION;
$entityManager = EntityManagerFactory::createInstance($options, $cacheDir, $isDevMode);

// Crear Request PSR-7
$request = ServerRequestFactory::fromGlobals();

// Crear y configurar Application
$app = new Application($config, $entityManager, $enviroment);
$app->addModule(AppModule::class);

// Ejecutar aplicación y emitir respuesta
$response = $app->run($request);
$emitter = new SapiEmitter();
$emitter->emit($response);
```

Agregar archivo config/doctrine.entities.php con el siguiente contenido

    <?php

    return  [
        "AppModule\Entities" => __DIR__."/../modules/AppModule/src/Entities",
    ];

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
        "entities"=> require __DIR__."/doctrine.entities.php"
    ];

Crear archivo cli-config.php con el siguiente código

    <?php

    use GPDCore\Factory\EntityManagerFactory;
    use Doctrine\ORM\Tools\Console\ConsoleRunner;

    require_once __DIR__."/vendor/autoload.php";
    $options = require __DIR__."/config/doctrine.local.php";
    $cacheDir = __DIR__ . "/data/DoctrineORMModule";
    $entityManager = EntityManagerFactory::createInstance($options, $cacheDir, true, '');

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

Actualizar archivo doctrine.entities.php con la ubicación de las entidades

    return  [
            "AppModule\Entities" => __DIR__."/../modules/AppModule/src/Entities",
    ]

Ejecutar el siguiente comando para generar el código SQL para actualizar la base de datos

    ./vendor/bin/doctrine orm:schema-tool:update --dump-sql

O Ejecutar el siguiente comando para actualizar la base de datos

    ./vendor/bin/doctrine orm:schema-tool:update --force

NOTA: Estos comandos no deben ser utilizados en producción

Iniciar con el comándo

    php -S localhost:8000 public/index.php

Para consultar api Graphql la ruta es http://localhost:8000/api

# API

### ConnectionTypeFactory

Clase que genera tipos connection para consultas de listas con paginación

#### Metodos

createConnectionType (\GraphQL\Type\Definition\ObjectType $type, string $name, string $description): \GraphQL\Type\Definition\ObjectType

Crea un tipo connection con los siguientes campos

    {
        totalCount: int!
        pageInfo: PaginationInput {
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

createEdgeType(\GraphQL\Type\Definition\ObjectType $nodeType): \GraphQL\Type\Definition\ObjectType

Crea un tipo Edge

    {
        cursor: string!,
        node: ObjectType!
    }

### ResolverFactory

Clase que facilita la creación de resolvers para operaciones CRUD comunes con Doctrine ORM.

#### Métodos principales

**forConnection(string $entityClass, ?QueryModifierInterface $queryModifier = null): callable**

Crea un resolver para consultas paginadas siguiendo el estándar Relay Connection.

```php
'Query::getUsers' => ResolverFactory::forConnection(User::class)
```

**forItem(string $entityClass): callable**

Crea un resolver para obtener un único elemento por ID.

```php
'Query::getUser' => ResolverFactory::forItem(User::class)
```

**forCreate(string $entityClass): callable**

Crea un resolver para operaciones de creación.

```php
'Mutation::createUser' => ResolverFactory::forCreate(User::class)
```

**forUpdate(string $entityClass): callable**

Crea un resolver para operaciones de actualización.

```php
'Mutation::updateUser' => ResolverFactory::forUpdate(User::class)
```

**forDelete(string $entityClass): callable**

Crea un resolver para operaciones de eliminación.

```php
'Mutation::deleteUser' => ResolverFactory::forDelete(User::class)
```

**forEntity(DataLoaderInterface $dataLoader, string $fieldName): callable**

Crea un resolver para relaciones many-to-one usando DataLoader (prevención N+1).

```php
$buffer = new EntityDataLoader(User::class, $entityManager);
'Post::author' => ResolverFactory::forEntity($buffer, 'author')
```

**forCollection(string $entityClass, string $fieldName, string $targetEntity, ?QueryModifierInterface $queryModifier = null): callable**

Crea un resolver para relaciones one-to-many usando DataLoader.

```php
'User::posts' => ResolverFactory::forCollection(User::class, 'posts', Post::class)
```

### ResolverMiddleware

Permite aplicar middleware a resolvers GraphQL para agregar lógica transversal (autenticación, logging, etc.).

#### Métodos

**wrap(callable $resolver, ResolverPipelineHandlerInterface|callable|null $middleware): callable**

Envuelve un resolver con un middleware.

```php
$authMiddleware = fn($next) => fn($root, $args, $context, $info) => {
    if (!$context->isAuthenticated()) {
        throw new UnauthorizedException();
    }
    return $next($root, $args, $context, $info);
};

'Query::protected' => ResolverMiddleware::wrap($resolver, $authMiddleware)
```

**chain(callable $resolver, array $middlewares): callable**

Aplica múltiples middlewares en secuencia.

```php
'Query::echo' => ResolverMiddleware::chain($echoResolver, [
    $authMiddleware,
    $loggingMiddleware,
    $cachingMiddleware
])
```

### MiddlewareCallable

Clase para crear middlewares reutilizables que implementan `ResolverPipelineHandlerInterface`.

```php
use GPDCore\Graphql\MiddlewareCallable;

$authMiddleware = MiddlewareCallable::create(function(callable $next) {
    return function($root, $args, $context, $info) use ($next) {
        // Lógica antes
        $result = $next($root, $args, $context, $info);
        // Lógica después
        return $result;
    };
});
```

