<?php

declare(strict_types=1);

namespace GPDCore\Core;

use GPDCore\Contracts\AppConfigInterface;
use GPDCore\Contracts\AppContextInterface;
use GPDCore\Contracts\ModuleProviderInterface;
use GPDCore\Contracts\ResolverManagerInterface;
use GPDCore\Core\MiddlewareQueue;
use GPDCore\Core\SchemaManager;
use GPDCore\Core\TypesManager;
use GPDCore\Graphql\ResolverPipeline;
use GPDCore\Routing\RouterInterface;
use Laminas\ServiceManager\ServiceManager;
use Psr\Http\Server\MiddlewareInterface;
use Webonyx\GraphQL\Type\Definition\ScalarType;

/**
 * Clase abstracta base para todos los módulos del sistema.
 * 
 * Proporciona la estructura y funcionalidad común que deben implementar
 * todos los módulos, incluyendo configuración, servicios, resolvers,
 * middlewares, rutas y tipos GraphQL.
 */
abstract class AbstractModule implements ModuleProviderInterface
{
    protected Application $application;

    /**
     * Obtiene la configuración específica del módulo.
     * 
     * @return array<string, mixed> Configuración del módulo en formato de array asociativo
     */
    abstract public function getConfig(): array;

    /**
     * Obtiene el esquema GraphQL del módulo.
     * 
     * @return string Definición del esquema GraphQL en formato SDL (Schema Definition Language)
     */
    abstract public function getSchema(): string;

    /**
     * Obtiene los servicios que necesita el módulo para el contenedor de dependencias.
     * 
     * @return array<string, array<string, class-string|callable>> Array de servicios organizados por tipo:
     *   - 'invokables': Clases que se pueden instanciar directamente
     *   - 'factories': Factories para crear servicios complejos  
     *   - 'aliases': Alias para servicios existentes
     */
    abstract public function getServices(): array;

    /**
     * Obtiene los resolvers GraphQL del módulo.
     * 
     * Los resolvers son funciones que resuelven las consultas, mutaciones
     * y subscripciones definidas en el esquema GraphQL.
     *
     * @return array<string, callable|ResolverPipeline> Array de resolvers donde:
     *   - La clave es el nombre del campo GraphQL (ej: 'Query.getUser')
     *   - El valor es una función callable o un ResolverPipeline
     */
    abstract public function getResolvers(): array;

    /**
     * Obtiene los middlewares HTTP del módulo.
     * 
     * Los middlewares se ejecutan en orden antes de procesar las peticiones,
     * permitiendo funcionalidad transversal como autenticación, logging, etc.
     *
     * @return array<MiddlewareInterface|class-string<MiddlewareInterface>> Array de middlewares
     */
    abstract public function getMiddlewares(): array;

    /**
     * Obtiene las rutas REST del módulo.
     * 
     * Define los endpoints HTTP que el módulo expone para operaciones REST.
     *
     * @return array<\GPDCore\Routing\RouteModel> Array de modelos de ruta
     */
    abstract public function getRoutes(): array;


    /**
     * Obtiene los tipos escalares GraphQL personalizados del módulo.
     * 
     * Los tipos escalares definen cómo se serializan/deserializan
     * valores primitivos en GraphQL (fechas, JSON, etc.).
     *
     * @return array<string, ScalarType|class-string<ScalarType>> Array donde:
     *   - La clave es el nombre del tipo en GraphQL
     *   - El valor es la instancia o clase que implementa el tipo
     */
    abstract public function getTypes(): array;

    /**
     * Registra los servicios del módulo en el contenedor de dependencias.
     * 
     * @param ServiceManager $serviceManager Contenedor de servicios de Laminas
     * @param AppContextInterface $context Contexto de la aplicación
     * @return void
     */
    public function registerServices(ServiceManager $serviceManager, AppContextInterface $context): void
    {
        $services = $this->getServices();
        foreach ($services as $type => $definitions) {
            foreach ($definitions as $key => $service) {
                switch ($type) {
                    case 'invokables':
                        $serviceManager->setInvokableClass($key, $service);
                        break;
                    case 'factories':
                        $serviceManager->setFactory($key, $service);
                        break;
                    case 'aliases':
                        $serviceManager->setAlias($key, $service);
                        break;
                }
            }
        }
    }

    /**
     * Registra la configuración del módulo en la configuración global de la aplicación.
     * 
     * @param AppConfigInterface $config Configuración global de la aplicación
     * @param AppContextInterface $context Contexto de la aplicación
     * @return void
     */
    public function registerConfig(AppConfigInterface $config, AppContextInterface $context): void
    {
        $moduleConfig = $this->getConfig();
        $config->add($moduleConfig);
    }

    /**
     * Registra los middlewares del módulo en la cola de middlewares de la aplicación.
     * 
     * @param MiddlewareQueue $queue Cola de middlewares HTTP
     * @param AppContextInterface $context Contexto de la aplicación
     * @return void
     */
    public function registerMiddleware(MiddlewareQueue $queue, AppContextInterface $context): void
    {
        $middlewares = $this->getMiddlewares();
        foreach ($middlewares as $middleware) {
            $queue->add($middleware);
        }
    }

    /**
     * Registra los resolvers GraphQL del módulo en el gestor de resolvers.
     * 
     * @param ResolverManagerInterface $resolverManager Gestor de resolvers GraphQL
     * @param AppContextInterface $context Contexto de la aplicación
     * @return void
     */
    public function registerResolvers(ResolverManagerInterface $resolverManager, AppContextInterface $context): void
    {
        $resolvers = $this->getResolvers();
        foreach ($resolvers as $key => $resolver) {
            $resolverManager->add($key, $resolver);
        }
    }
    /**
     * Registra las rutas Rest del módulo en el gestor de rutas.
     * 
     * @param RouterInterface $routeManager Gestor de rutas REST
     * @param AppContextInterface $context Contexto de la aplicación
     * @return void
     */
    public function registerRoutes(RouterInterface $routeManager, AppContextInterface $context): void
    {
        $routes = $this->getRoutes();
        foreach ($routes as $route) {
            $routeManager->add($route);
        }
    }

    /**
     * Registra los tipos escalares GraphQL del módulo en el gestor de tipos.
     * 
     * @param TypesManager $typesManager Gestor de tipos escalares GraphQL
     * @param AppContextInterface $context Contexto de la aplicación
     * @return void
     */
    public function registerType(TypesManager $typesManager, AppContextInterface $context): void
    {
        $types = $this->getTypes();
        foreach ($types as $key => $type) {
            $typesManager->add($key, $type);
        }
    }

    /**
     * Registra el fragmento de esquema GraphQL del módulo en el gestor de esquemas.
     * 
     * @param SchemaManager $schemaManager Gestor de esquemas GraphQL
     * @param AppContextInterface $context Contexto de la aplicación
     * @return void
     */
    public function registerSchemaChunk(SchemaManager $schemaManager, AppContextInterface $context): void
    {
        $schemaChunk = $this->getSchema();
        if (!empty($schemaChunk)) {
            $schemaManager->add($schemaChunk);
        }
    }

    /**
     * Establece la instancia de la aplicación en el módulo.
     * 
     * @param Application $application Instancia de la aplicación
     * @return void
     */
    public function setApplication(Application $application): void
    {
        $this->application = $application;
    }

    /**
     * Obtiene el contexto de la aplicación.
     * 
     * @return AppContextInterface|null Contexto de la aplicación o null si no está disponible
     */
    public function getAppContext(): ?AppContextInterface
    {
        return $this->application->getContext();
    }

    /**
     * Obtiene la instancia de la aplicación.
     * 
     * @return Application Instancia de la aplicación
     */
    public function getApplication(): Application
    {
        return $this->application;
    }
}
