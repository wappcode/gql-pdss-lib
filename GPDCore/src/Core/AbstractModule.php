<?php

declare(strict_types=1);

namespace GPDCore\Core;

use GPDCore\Contracts\AppConfigInterface;
use GPDCore\Contracts\AppContextInterface;
use GPDCore\Contracts\ModuleProviderInterface;
use GPDCore\Contracts\ResolverManagerInterface;
use Laminas\ServiceManager\ServiceManager;

abstract class AbstractModule implements ModuleProviderInterface
{
    protected AppContextInterface $context;

    /**
     * Array con la configuración del módulo.
     */
    abstract public function getConfig(): array;

    /**
     * Array con la configuración del módulo.
     */
    abstract public function getSchema(): string;

    /**
     * Array con los servicios y tipos graphql que se necesitan para el módulo
     * El indice se utiliza como nombre del tipo.
     *
     * @return array [invokables => [key: service], factories => [key: service], aliases => [key: service]]
     */
    abstract public function getServices(): array;

    /**
     * Array con los resolvers del módulo.
     *
     * @return array array(string $key => callable $resolver)
     */
    abstract public function getResolvers(): array;

    abstract public function getMiddlewares(): array;

    /**
     * Array con los tipos scalar graphql del módulo. El indice se utiliza como nombre del tipo.
     *
     * @return array
     */
    abstract public function getTypes(): array;

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

    public function registerConfig(AppConfigInterface $config, AppContextInterface $context): void
    {
        $moduleConfig = $this->getConfig();
        $config->add($moduleConfig);
    }

    public function registerMiddleware(MiddlewareQueue $queue, AppContextInterface $context): void
    {
        $middlewares = $this->getMiddlewares();
        foreach ($middlewares as $middleware) {
            $queue->add($middleware);
        }
    }

    public function registerResolvers(ResolverManagerInterface $resolverManager, AppContextInterface $context): void
    {
        $resolvers = $this->getResolvers();
        foreach ($resolvers as $key => $resolver) {
            $resolverManager->add($key, $resolver);
        }
    }

    public function registerType(TypesManager $typesManager, AppContextInterface $context): void
    {
        $types = $this->getTypes();
        foreach ($types as $key => $type) {
            $typesManager->add($key, $type);
        }
    }

    public function registerSchemaChunk(SchemaManager $schemaManager, AppContextInterface $context): void
    {
        $schemaChunk = $this->getSchema();
        if (!empty($schemaChunk)) {
            $schemaManager->add($schemaChunk);
        }
    }

    public function registerModule(
        SchemaManager $schemaManager,
        ResolverManagerInterface $resolverManager,
        MiddlewareQueue $middlewareQueue,
        TypesManager $typesManager,
        AppConfigInterface $config,
        AppContextInterface $context,
        ?ServiceManager $serviceManager,
    ): void {
        $this->context = $context;
        $this->registerMiddleware($middlewareQueue, $context);
        $this->registerResolvers($resolverManager, $context);
        $this->registerType($typesManager, $context);
        $this->registerSchemaChunk($schemaManager, $context);
        $this->registerConfig($config, $context);
        if ($serviceManager) {
            $this->registerServices($serviceManager, $context);
        }
    }
}
