<?php

declare(strict_types=1);

namespace GPDCore\Application\Core;

use GPDCore\Contracts\AppConfigInterface;
use GPDCore\Contracts\AppContextInterface;
use GPDCore\Contracts\ModuleProviderInterface;
use GPDCore\Contracts\ResolverManagerInterface;
use GPDCore\Application\Internal\SchemaManager;
use GPDCore\Application\Internal\TypesManager;
use GPDCore\Application\Core\Application;
use GPDCore\Contracts\MiddlewareQueueInterface;
use GPDCore\Routing\RouterInterface;
use Laminas\ServiceManager\ServiceManager;

abstract class AbstractModule implements ModuleProviderInterface
{
    protected Application $application;

    abstract public function getConfig(): array;
    abstract public function getSchema(): string;
    abstract public function getServices(): array;
    abstract public function getResolvers(): array;
    abstract public function getMiddlewares(): array;
    abstract public function getRoutes(): array;
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

    public function registerMiddleware(MiddlewareQueueInterface $queue, AppContextInterface $context): void
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

    public function registerRoutes(RouterInterface $routeManager, AppContextInterface $context): void
    {
        $routes = $this->getRoutes();
        foreach ($routes as $route) {
            $routeManager->add($route);
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

    public function setApplication(Application $application): void
    {
        $this->application = $application;
    }

    public function getAppContext(): ?AppContextInterface
    {
        return $this->application->getContext();
    }

    public function getApplication(): Application
    {
        return $this->application;
    }
}
