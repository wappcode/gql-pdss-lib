# GQLPDSS-lib

**Una librería PHP moderna para crear APIs GraphQL escalables con Doctrine ORM, arquitectura modular y funcionalidades avanzadas como DataLoaders y middleware.**

[![Versión](https://img.shields.io/badge/version-5.0.0-blue)](https://github.com/wappcode/gql-pdss-lib)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4)](https://www.php.net/)
[![Doctrine](https://img.shields.io/badge/Doctrine-ORM%203-orange)](https://www.doctrine-project.org/)
[![GraphQL](https://img.shields.io/badge/GraphQL-15.19.1-E10098)](https://graphql.org/)

## 📚 Documentación Completa

Para información detallada, visita: [Quick Start Guide](https://wappcode.github.io/gql-pdss-lib-docs?1)

## ✨ Características Principales

- 🚀 **API GraphQL completa** 
- 🏗️ **Arquitectura modular** flexible y escalable  
- 🔄 **Resolvers automáticos** para operaciones CRUD con Doctrine ORM
- ⚡ **DataLoaders integrados** para prevenir el problema N+1
- 🔧 **Middleware pipeline** para lógica transversal (auth, logging, cache)
- 📄 **Paginación estilo Relay** con cursor-based pagination
- 🎯 **Tipos GraphQL personalizados** (DateTime, Date, JSON)
- 🐳 **Entorno Docker** preconfigurado para desarrollo
- 📋 **Sistema de filtros avanzado** con múltiples operadores

## 🛠️ Instalación

### Usar Composer

### 1. Crear nuevo proyecto

```bash
composer init
```

### 2. Instalar la librería

```bash
composer require wappcode/gqlpdss:^5.0.0
```

O agregar al `composer.json`:

```json
{
    "name": "mi-proyecto/graphql-api",
    "type": "project",
    "require": {
        "wappcode/gqlpdss": "^5.0.0"
    },
    "require-dev": {
        "phpunit/phpunit": "^10.0"
    }
}
```

```bash
composer install
```

### 3. Estructura del proyecto

Crea la siguiente estructura de directorios:

```
mi-proyecto/
├── config/
│   ├── master.config.php
│   ├── doctrine.local.php
│   └── doctrine.entities.php
├── data/
│   └── DoctrineORMModule/
├── modules/
│   └── AppModule/
│       ├── config/
│       │   ├── module.config.php
│       │   └── schema.graphql
│       └── src/
│           ├── AppModule.php
│           ├── Entities/
│           ├── Graphql/
│           └── Services/
├── public/
│   └── index.php
├── cli-config.php
└── composer.json
```

## ⚙️ Configuración

### 1. Configurar el módulo principal

#### Crear `modules/AppModule/config/module.config.php`

```php
<?php
return [
    // Configuración específica del módulo
    'version' => '1.0.0',
    'description' => 'Módulo principal de la aplicación'
];
```

#### Crear `modules/AppModule/src/AppModule.php`

```php
<?php

namespace AppModule;

use AppModule\Entities\User;
use DateTime;
use GPDCore\Contracts\AppContextInterface;
use GPDCore\Core\AbstractModule;
use GPDCore\Graphql\ResolverFactory;
use GPDCore\Graphql\ResolverPipelineFactory;

class AppModule extends AbstractModule
{
    /**
     * Configuración del módulo
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
        $schema = file_get_contents(__DIR__ . '/../config/schema.graphql');
        return $schema ?: '';
    }

    /**
     * Servicios del módulo para ServiceManager
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
     * Tipos GraphQL personalizados
     */
    public function getTypes(): array
    {
        return [];
    }

    /**
     * Middlewares HTTP del módulo
     */
    public function getMiddlewares(): array
    {
        return [];
    }

    /**
     * Rutas REST del módulo (opcional)
     */
    public function getRoutes(): array
    {
        return [];
    }

    /**
     * Resolvers GraphQL del módulo
     */
    public function getResolvers(): array
    {
        // Middleware de ejemplo
        $proxyEcho1 = fn($resolver) => fn($root, $args, $context, $info) => 
            'Proxy 1 ' . $resolver($root, $args, $context, $info);
        $proxyEcho2 = fn($resolver) => fn($root, $args, $context, $info) => 
            'Proxy 2 ' . $resolver($root, $args, $context, $info);
        $echoResolve = fn($root, $args, $context, $info) => $args['msg'];

        return [
            // Resolver simple
            'Query::showDate' => fn($root, $args, AppContextInterface $context, $info) => new DateTime(),
            'Query::echo' => $echoResolve,
            
            // Resolvers con middleware pipeline
            'Query::echoProxy' => ResolverPipelineFactory::createPipeline($echoResolve, [
                ResolverPipelineFactory::createWrapper($proxyEcho1),
            ]),
            'Query::echoProxies' => ResolverPipelineFactory::createPipeline($echoResolve, [
                ResolverPipelineFactory::createWrapper($proxyEcho2),
                ResolverPipelineFactory::createWrapper($proxyEcho1),
            ]),
            
            // Resolvers CRUD automáticos usando ResolverFactory
            'Query::getUsers' => ResolverFactory::forConnection(User::class),
            'Query::getUser' => ResolverFactory::forItem(User::class),
            'Mutation::createUser' => ResolverFactory::forCreate(User::class),
            'Mutation::updateUser' => ResolverFactory::forUpdate(User::class),
            'Mutation::deleteUser' => ResolverFactory::forDelete(User::class),
        ];
    }

    /**
     * Campos Query adicionales (opcional)
     */
    public function getQueryFields(): array
    {
        return [];
    }
}
```

#### Configurar el autoload en `composer.json`

```json
{
    "autoload": {
        "psr-4": {
            "AppModule\\": "modules/AppModule/src/"
        }
    }
}
```

```bash
composer dump-autoload -o
```

### 2. Archivos de configuración

#### Crear `config/master.config.php`

```php
<?php
return [
    // Configuración general de la aplicación
    'app' => [
        'name' => 'Mi API GraphQL',
        'version' => '1.0.0',
        'debug' => false
    ],
    
];
```

#### Crear `config/doctrine.entities.php`

```php
<?php
return [
    "AppModule\\Entities" => __DIR__ . "/../modules/AppModule/src/Entities",
];
```

#### Crear `config/doctrine.local.php`

```php
<?php
return [
    "driver" => [
        'user'     => 'root',
        'password' => 'password',
        'dbname'   => 'mi_database',
        'driver'   => 'pdo_mysql',
        'host'     => '127.0.0.1',
        'charset'  => 'utf8mb4'
    ],
    "entities" => require __DIR__ . "/doctrine.entities.php"
];
```

#### Crear `public/index.php`

```php
<?php

use AppModule\AppModule;
use GPDCore\Contracts\AppContextInterface;
use GPDCore\Core\AppConfig;
use GPDCore\Core\Application;
use GPDCore\Factory\EntityManagerFactory;
use GraphqlModule\GraphqlModule;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\ServiceManager\ServiceManager;

require_once __DIR__ . '/../vendor/autoload.php';

// Configuración
$configFile = __DIR__ . '/../config/doctrine.local.php';
$cacheDir = __DIR__ . '/../data/DoctrineORMModule';
$environment = getenv('APP_ENV') ?: AppContextInterface::ENV_DEVELOPMENT;

// Cargar configuración
$masterConfig = require __DIR__ . '/../config/master.config.php';
$config = AppConfig::getInstance()->setMasterConfig($masterConfig);

// Inicializar ServiceManager
$serviceManager = new ServiceManager();

// Crear EntityManager
$entityManagerOptions = file_exists($configFile) ? require $configFile : [];
$isEntityManagerDevMode = $environment !== AppContextInterface::ENV_PRODUCTION;
$entityManager = EntityManagerFactory::createInstance(
    $entityManagerOptions, 
    $cacheDir, 
    $isEntityManagerDevMode
);

// Crear Request PSR-7
$request = ServerRequestFactory::fromGlobals();

// Crear y configurar Application
$app = new Application($config, $entityManager, $environment);

// Registrar módulos
$app->addModule(new GraphqlModule(route: '/api'))    // GraphQL endpoint
   ->addModule(AppModule::class);                      // Módulo principal

// Ejecutar aplicación y emitir respuesta
$response = $app->run($request);
$emitter = new SapiEmitter();
$emitter->emit($response);
```

#### Crear `cli-config.php` (para comandos Doctrine CLI)

```php
<?php

use GPDCore\Factory\EntityManagerFactory;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

require_once __DIR__ . "/vendor/autoload.php";

$options = require __DIR__ . "/config/doctrine.local.php";
$cacheDir = __DIR__ . "/data/DoctrineORMModule";
$entityManager = EntityManagerFactory::createInstance($options, $cacheDir, true);

return ConsoleRunner::createHelperSet($entityManager);
```

## 💾 Trabajando con Entidades Doctrine

### Ejemplo de Entidad User

Crear `modules/AppModule/src/Entities/User.php`:

```php
<?php

namespace AppModule\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use GPDCore\Entities\AbstractEntityModelStringId;

#[ORM\Entity()]
#[ORM\Table(name: 'users')]
class User extends AbstractEntityModelStringId
{
    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255)]
    private string $email;

    #[ORM\JoinTable(name: 'users_accounts')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\InverseJoinColumn(name: 'account_code', referencedColumnName: 'code', nullable: false)]
    #[ORM\ManyToMany(targetEntity: Account::class)]
    private Collection $accounts;

    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'user')]
    private Collection $posts;

    public function __construct()
    {
        parent::__construct();
        $this->accounts = new ArrayCollection();
        $this->posts = new ArrayCollection();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getAccounts(): Collection
    {
        return $this->accounts;
    }

    public function getPosts(): Collection
    {
        return $this->posts;
    }
}
```

### Comandos Doctrine útiles

```bash
# Generar SQL para actualizar la base de datos
./vendor/bin/doctrine orm:schema-tool:update --dump-sql

# Actualizar la base de datos (⚠️  Solo en desarrollo)
./vendor/bin/doctrine orm:schema-tool:update --force

# Crear migración
./vendor/bin/doctrine migrations:diff

# Ejecutar migraciones
./vendor/bin/doctrine migrations:migrate

# Validar mapping
./vendor/bin/doctrine orm:validate-schema
```

## 🚀 Ejecutar la aplicación

### Desarrollo local

```bash
# Servidor de desarrollo PHP
php -S localhost:8000 public/index.php

```

### Endpoints disponibles

- **GraphQL API**: 
  - `GET/POST http://localhost:8000/api` (desarrollo)
  - `POST http://localhost:8000/api` (producción)

## 📋 Schema GraphQL básico

Crear `modules/AppModule/config/schema.graphql`:

```graphql
type Query {
    # Consultas básicas
    showDate: DateTime!
    echo(msg: String!): String!
    echoProxy(msg: String!): String!
    echoProxies(msg: String!): String!
    
    # CRUD de usuarios
    getUsers(
        pagination: PaginationInput
        filters: [FilterGroupInput!]
        joins: [JoinInput!]
        orderBy: [OrderByInput!]
    ): UserConnection!
    
    getUser(id: ID!): User
}

type Mutation {
    createUser(input: UserInput!): User!
    updateUser(id: ID!, input: UserInput!): User!
    deleteUser(id: ID!): Boolean!
}

type User {
    id: ID!
    name: String!
    email: String!
    accounts: [Account!]!
    posts: [Post!]!
    createdAt: DateTime!
    updatedAt: DateTime!
}

input UserInput {
    name: String!
    email: String!
}

# Tipos de conexión para paginación
type UserConnection {
    totalCount: Int!
    pageInfo: PageInfo!
    edges: [UserEdge!]!
}

type UserEdge {
    cursor: String!
    node: User!
}

type PageInfo {
    hasNextPage: Boolean!
    hasPreviousPage: Boolean!
    startCursor: String
    endCursor: String
}

# Tipos escalares personalizados (incluidos automáticamente)
scalar DateTime
scalar Date  
scalar JSONData
```

# 📚 API Reference

## 🔧 ResolverFactory

La clase `ResolverFactory` simplifica la creación de resolvers CRUD automáticos con Doctrine ORM.

### Métodos principales

#### `forConnection(string $entityClass, ?QueryModifierInterface $queryModifier = null): callable`

Crea un resolver para consultas paginadas estilo Relay Connection con soporte completo para filtros, ordenamiento y joins.

```php
// Resolver básico
'Query::getUsers' => ResolverFactory::forConnection(User::class)

// Con modificador de query personalizado
'Query::getActiveUsers' => ResolverFactory::forConnection(
    User::class, 
    new class implements QueryModifierInterface {
        public function modify(QueryBuilder $qb, array $args): QueryBuilder {
            return $qb->andWhere('entity.status = :status')
                     ->setParameter('status', 'active');
        }
    }
)
```

**Ejemplo de uso en GraphQL:**

```graphql
query GetUsers {
    getUsers(
        pagination: { first: 10, after: "cursor123" }
        filters: [{
            conditions: [{
                property: "name"
                filterOperator: LIKE
                value: { single: "%John%" }
            }]
        }]
        orderBy: [{ property: "createdAt", direction: DESC }]
    ) {
        totalCount
        pageInfo {
            hasNextPage
            hasPreviousPage
            startCursor
            endCursor
        }
        edges {
            cursor
            node {
                id
                name
                email
                createdAt
            }
        }
    }
}
```

#### `forItem(string $entityClass): callable`

Crea un resolver para obtener un único elemento por ID.

```php
'Query::getUser' => ResolverFactory::forItem(User::class)
```

```graphql
query GetUser {
    getUser(id: "user-123") {
        id
        name
        email
    }
}
```

#### `forCreate(string $entityClass): callable`

Crea un resolver para operaciones de creación con validación automática.

```php
'Mutation::createUser' => ResolverFactory::forCreate(User::class)
```

```graphql
mutation CreateUser {
    createUser(input: {
        name: "John Doe"
        email: "john@example.com"
    }) {
        id
        name
        email
        createdAt
    }
}
```

#### `forUpdate(string $entityClass): callable`

Crea un resolver para operaciones de actualización.

```php
'Mutation::updateUser' => ResolverFactory::forUpdate(User::class)
```

```graphql
mutation UpdateUser {
    updateUser(
        id: "user-123"
        input: {
            name: "Jane Doe"
            email: "jane@example.com"
        }
    ) {
        id
        name
        email
        updatedAt
    }
}
```

#### `forDelete(string $entityClass): callable`

Crea un resolver para operaciones de eliminación (soft delete si está configurado).

```php
'Mutation::deleteUser' => ResolverFactory::forDelete(User::class)
```

```graphql
mutation DeleteUser {
    deleteUser(id: "user-123")
}
```

### Resolvers para relaciones (prevención N+1)

#### `forEntity(DataLoaderInterface $dataLoader, string $fieldName): callable`

Crea un resolver para relaciones many-to-one usando DataLoader.

```php
use GPDCore\DataLoaders\EntityDataLoader;

$userDataLoader = new EntityDataLoader(User::class, $entityManager);

// En el módulo
'Post::author' => ResolverFactory::forEntity($userDataLoader, 'author')
```

#### `forCollection(string $entityClass, string $fieldName, string $targetEntity, ?QueryModifierInterface $queryModifier = null): callable`

Crea un resolver para relaciones one-to-many usando DataLoader.

```php
'User::posts' => ResolverFactory::forCollection(User::class, 'posts', Post::class)
'User::activePosts' => ResolverFactory::forCollection(
    User::class, 
    'posts', 
    Post::class,
    new class implements QueryModifierInterface {
        public function modify(QueryBuilder $qb, array $args): QueryBuilder {
            return $qb->andWhere('target.status = :status')
                     ->setParameter('status', 'published');
        }
    }
)
```
<!-- TODO: Documentar forCollectionCount -->

## 🔄 ResolverPipelineFactory

Sistema de middleware para resolvers GraphQL que permite aplicar lógica transversal.

### Métodos principales

#### `createPipeline(callable $resolver, array $middlewares): callable`

Crea un pipeline de middleware para un resolver.

```php
// Middleware de ejemplo
$authMiddleware = fn($resolver) => fn($root, $args, $context, $info) => {
    if (!$context->isAuthenticated()) {
        throw new UnauthorizedException('Authentication required');
    }
    return $resolver($root, $args, $context, $info);
};

$loggingMiddleware = fn($resolver) => fn($root, $args, $context, $info) => {
    $startTime = microtime(true);
    $result = $resolver($root, $args, $context, $info);
    $duration = microtime(true) - $startTime;
    error_log("Resolver {$info->fieldName} executed in {$duration}s");
    return $result;
};

// Aplicar middlewares (se ejecutan en orden inverso)
'Query::protectedData' => ResolverPipelineFactory::createPipeline($baseResolver, [
    ResolverPipelineFactory::createWrapper($loggingMiddleware),
    ResolverPipelineFactory::createWrapper($authMiddleware),
])
```

#### `createWrapper(callable $middleware): ResolverPipelineHandlerInterface`

Convierte una función middleware en un handler de pipeline.

```php
$cacheMiddleware = fn($resolver) => fn($root, $args, $context, $info) => {
    $cacheKey = "resolver_{$info->fieldName}_" . md5(serialize($args));
    
    if ($cached = $context->getCache()->get($cacheKey)) {
        return $cached;
    }
    
    $result = $resolver($root, $args, $context, $info);
    $context->getCache()->set($cacheKey, $result, 300); // 5 min
    
    return $result;
};

$wrappedMiddleware = ResolverPipelineFactory::createWrapper($cacheMiddleware);
```


## 🎯 Tipos GraphQL personalizados

La librería incluye tipos escalares personalizados listos para usar:

### DateTimeType
- **Nombre**: `DateTime`
- **Descripción**: Fecha y hora en formato ISO 8601
- **Ejemplo**: `"2024-01-15T10:30:00Z"`

### DateType  
- **Nombre**: `Date`
- **Descripción**: Fecha en formato ISO (solo fecha)
- **Ejemplo**: `"2024-01-15"`

### JSONData
- **Nombre**: `JSONData` 
- **Descripción**: Datos JSON arbitrarios
- **Ejemplo**: `{"key": "value", "nested": {"data": 123}}`

### Registro de tipos en módulos

```php
public function getTypes(): array
{
    return [
        DateType::NAME => DateType::class,
        DateTimeType::NAME => DateTimeType::class,
        JSONData::NAME => JSONData::class,
        // Tus tipos personalizados
        'MyCustomType' => MyCustomType::class,
    ];
}
```

## 🔍 Sistema de filtros avanzado

La librería incluye un sistema de filtros robusto que soporta operadores complejos, joins y lógica AND/OR.

### Operadores disponibles

```graphql
enum FilterOperator {
  EQUAL
  NOT_EQUAL  
  BETWEEN
  GREATER_THAN
  LESS_THAN
  GREATER_EQUAL_THAN
  LESS_EQUAL_THAN
  LIKE
  NOT_LIKE
  IN
  NOT_IN
}
```

### Ejemplo de filtros complejos

```graphql
query GetFilteredUsers {
  getUsers(
    # Filtros con lógica AND/OR
    filters: [{
      groupLogic: AND
      conditionsLogic: OR
      conditions: [
        {
          property: "name"
          filterOperator: LIKE
          value: { single: "%John%" }
        }
        {
          property: "email"
          filterOperator: LIKE  
          value: { single: "%gmail%" }
        }
      ]
    }]
    
    # Joins para filtrar por propiedades relacionadas
    joins: [{
      property: "posts"
      joinType: INNER
      alias: "userPosts"
    }]
    
    # Ordenamiento
    orderBy: [{
      property: "createdAt"
      direction: DESC
    }]
    
    # Paginación
    pagination: {
      first: 20
      after: "cursor123"
    }
  ) {
    totalCount
    edges {
      node {
        id
        name
        email
        posts {
          id
          title
        }
      }
    }
  }
}
```

## 🚀 Ejemplos prácticos

### 1. API completa de Blog

```php
// modules/AppModule/src/AppModule.php
class AppModule extends AbstractModule
{
    public function getResolvers(): array
    {
        return [
            // Consultas básicas
            'Query::getPosts' => ResolverFactory::forConnection(Post::class),
            'Query::getPost' => ResolverFactory::forItem(Post::class),
            'Query::getUsers' => ResolverFactory::forConnection(User::class),
            'Query::getUser' => ResolverFactory::forItem(User::class),
            
            // Mutaciones
            'Mutation::createPost' => ResolverFactory::forCreate(Post::class),
            'Mutation::updatePost' => ResolverFactory::forUpdate(Post::class),
            'Mutation::deletePost' => ResolverFactory::forDelete(Post::class),
            
            // Relaciones (prevención N+1)
            'Post::author' => ResolverFactory::forEntity(
                new EntityDataLoader(User::class, $this->entityManager), 
                'author'
            ),
            'User::posts' => ResolverFactory::forCollection(
                User::class, 
                'posts', 
                Post::class
            ),
            
            // Resolver personalizado con middleware
            'Mutation::publishPost' => ResolverPipelineFactory::createPipeline(
                function($root, $args, AppContextInterface $context, $info) {
                    $post = $context->getEntityManager()
                        ->getRepository(Post::class)
                        ->find($args['id']);
                    
                    if (!$post) {
                        throw new \Exception('Post not found');
                    }
                    
                    $post->setStatus('published');
                    $context->getEntityManager()->flush();
                    
                    return $post;
                },
                [
                    ResolverPipelineFactory::createWrapper($this->getAuthMiddleware()),
                    ResolverPipelineFactory::createWrapper($this->getOwnershipMiddleware()),
                ]
            ),
        ];
    }
    
    private function getAuthMiddleware(): callable
    {
        return fn($resolver) => fn($root, $args, $context, $info) => {
            if (!$context->getCurrentUser()) {
                throw new \Exception('Authentication required');
            }
            return $resolver($root, $args, $context, $info);
        };
    }
    
    private function getOwnershipMiddleware(): callable
    {
        return fn($resolver) => fn($root, $args, $context, $info) => {
            $post = $context->getEntityManager()
                ->getRepository(Post::class)
                ->find($args['id']);
                
            if ($post && $post->getAuthor()->getId() !== $context->getCurrentUser()->getId()) {
                throw new \Exception('Access denied');
            }
            
            return $resolver($root, $args, $context, $info);
        };
    }
}
```

### 2. GraphQL Schema completo

```graphql
# modules/AppModule/config/schema.graphql

type Query {
    # Posts
    getPosts(
        pagination: PaginationInput
        filters: [FilterGroupInput!]
        orderBy: [OrderByInput!]
    ): PostConnection!
    
    getPost(id: ID!): Post
    getPublishedPosts: PostConnection!
    
    # Users  
    getUsers(
        pagination: PaginationInput
        filters: [FilterGroupInput!]
    ): UserConnection!
    
    getUser(id: ID!): User
    me: User
}

type Mutation {
    # Authentication
    login(email: String!, password: String!): AuthPayload!
    register(input: RegisterInput!): AuthPayload!
    
    # Posts
    createPost(input: PostInput!): Post!
    updatePost(id: ID!, input: PostInput!): Post!
    deletePost(id: ID!): Boolean!
    publishPost(id: ID!): Post!
    
    # Users
    updateProfile(input: UserUpdateInput!): User!
}

type User {
    id: ID!
    name: String!
    email: String!
    posts(status: PostStatus): [Post!]!
    createdAt: DateTime!
    updatedAt: DateTime!
}

type Post {
    id: ID!
    title: String!
    content: String!
    status: PostStatus!
    author: User!
    comments: [Comment!]!
    createdAt: DateTime!
    updatedAt: DateTime!
    publishedAt: DateTime
}

enum PostStatus {
    DRAFT
    PUBLISHED
    ARCHIVED
}

type AuthPayload {
    token: String!
    user: User!
}

input PostInput {
    title: String!
    content: String!
    status: PostStatus = DRAFT
}

input UserUpdateInput {
    name: String
    email: String
}

input RegisterInput {
    name: String!
    email: String!
    password: String!
}
```

## 📝 Mejores prácticas

### 1. Organización del código

```
modules/AppModule/
├── config/
│   ├── module.config.php
│   └── schema.graphql
├── src/
│   ├── AppModule.php
│   ├── Entities/
│   │   ├── User.php
│   │   ├── Post.php
│   │   └── Comment.php
│   ├── Graphql/
│   │   ├── Resolvers/
│   │   │   ├── UserResolvers.php
│   │   │   ├── PostResolvers.php
│   │   │   └── CommentResolvers.php
│   │   ├── Types/
│   │   │   └── CustomScalar.php
│   │   └── Middleware/
│   │       ├── AuthMiddleware.php
│   │       └── RateLimitMiddleware.php
│   └── Services/
│       ├── UserService.php
│       └── PostService.php
```

### 2. Uso de DataLoaders

```php
// Evita el problema N+1
class UserResolvers
{
    private EntityDataLoader $userDataLoader;
    
    public function __construct(EntityManager $em)
    {
        $this->userDataLoader = new EntityDataLoader(User::class, $em);
    }
    
    public static function getPostsAuthorResolver(): callable 
    {
        return ResolverFactory::forEntity($this->userDataLoader, 'author');
    }
}
```



### 4. Manejo de errores

```php
use GPDCore\Exceptions\GQLException;

'Query::sensitiveData' => function($root, $args, AppContextInterface $context, $info) {
    try {
        if (!$context->getCurrentUser()) {
            throw new GQLException('Not authenticated', 'UNAUTHENTICATED');
        }
        
        if (!$context->getCurrentUser()->hasRole('admin')) {
            throw new GQLException('Insufficient permissions', 'FORBIDDEN');
        }
        
        return $this->getSensitiveData();
        
    } catch (\Exception $e) {
        throw new GQLException(
            'Failed to fetch sensitive data: ' . $e->getMessage(),
            'INTERNAL_ERROR'
        );
    }
}
```

## 🤝 Contribuir

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/amazing-feature`)
3. Commit tus cambios (`git commit -m 'Add amazing feature'`)
4. Push a la rama (`git push origin feature/amazing-feature`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 🆘 Soporte

- 📖 [Documentación completa](https://wappcode.github.io/gql-pdss-lib-docs)
- 🐛 [Reportar issues](https://github.com/wappcode/gql-pdss-lib/issues)
- 💬 [Discusiones](https://github.com/wappcode/gql-pdss-lib/discussions)

---

**¿Te ha sido útil esta librería?** ⭐ ¡Danos una estrella en GitHub!

