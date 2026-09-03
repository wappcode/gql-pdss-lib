<?php

declare(strict_types=1);

namespace GPDCore\Application\Core;

use Doctrine\ORM\EntityManager;
use Exception;
use GPDCore\Contracts\AppConfigInterface;
use GPDCore\Contracts\AppContextInterface;
use GPDCore\Contracts\ResolverManagerInterface;
use GPDCore\Routing\AppRouter;
use GPDCore\Routing\RouterInterface;
use Laminas\ServiceManager\ServiceManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use GPDCore\Application\Internal\TypesManager;
use GPDCore\Application\Internal\SchemaManager;
use GPDCore\Application\Internal\ResolverManager;
use GPDCore\Application\Internal\MiddlewareQueue;
use GPDCore\Application\Internal\FrameworkHandler;

class Application
{
    protected array $modules = [];
    protected RouterInterface $router;
    protected bool $started = false;
    protected bool $productionMode = false;
    protected AppConfigInterface $config;
    protected AppContextInterface $context;
    protected SchemaManager $schemaManager;
    protected TypesManager $typesManager;
    protected ResolverManagerInterface $resolverManager;
    protected string $enviroment;
    protected ?EntityManager $entityManager;
    protected string $baseHref = '';
    protected ?ServiceManager $serviceManager = null;
    protected ?ServerRequestInterface $request = null;
    protected MiddlewareQueue $middlewareQueue;

    public function __construct(AppConfigInterface $config, ?EntityManager $entityManager, string $enviroment = 'development', string $baseHref = '')
    {
        $this->config = $config;
        $this->entityManager = $entityManager;
        $this->enviroment = $enviroment;
        $this->baseHref = $baseHref;
        $this->serviceManager = new ServiceManager();
        $this->resolverManager = new ResolverManager();
        $this->typesManager = $this->createTypeManager();
        $this->schemaManager = new SchemaManager();
        $this->router = new AppRouter();
        $this->middlewareQueue = $this->createMiddlewareQueue();
        $this->productionMode = $enviroment === AppContextInterface::ENV_PRODUCTION;
    }

    public function addModule(string|\GPDCore\Application\Core\AbstractModule $module): \GPDCore\Application\Core\Application
    {
        if ($this->started) {
            throw new Exception('Solo se puede asignar los módulos antes de que la aplicación inicie');
        }
        if (is_string($module)) {
            $module = new $module($this);
        }
        array_push($this->modules, $module);

        return $this;
    }

    public function getContext(): AppContextInterface
    {
        if (!$this->context) {
            throw new Exception('El contexto de la aplicación no ha sido creado aún. Ejecuta el método run() de la aplicación primero.');
        }

        return $this->context;
    }

    public function withContextAttribute(string $name, mixed $value): void
    {
        $context = $this->getContext();
        $context = $context->withContextAttribute($name, $value);
        $this->context = $context;
    }

    public function run(ServerRequestInterface $request): ResponseInterface
    {
        $this->applyProductionMode();
        $this->context = $this->createContext();
        $this->withContextAttribute(\GPDCore\Application\Core\Application::class, $this);
        $this->setApplicationToModules($this);
        $this->registerModulesConfig($this->context);
        $this->registerModulesServices($this->context);
        $this->registerModulesMiddleware($this->middlewareQueue, $this->context);
        $this->registerModulesRoutesAndGraphQLConfig($this->context);
        $this->request = $request->withAttribute(\GPDCore\Application\Core\Application::class, $this);
        $response = $this->middlewareQueue->handle($this->request);

        return $response;
    }

    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        $this->started = true;
        $this->withContextAttribute(ServerRequestInterface::class, $request);
        return $this->router->dispatch($request);
    }

    public function isProductionMode(): bool
    {
        return $this->productionMode;
    }

    protected function applyProductionMode(): void
    {
        if ($this->productionMode) {
            ini_set('display_errors', '0');
            error_reporting(0);
        } else {
            ini_set('display_errors', '1');
        }
    }

    public function getEnviroment(): string
    {
        return $this->enviroment;
    }

    public function getBaseHref(): string
    {
        return $this->baseHref;
    }

    protected function createMiddlewareQueue(): MiddlewareQueue
    {
        $frameworkHandler = new FrameworkHandler($this);
        $middlewareQueue = new MiddlewareQueue($frameworkHandler);

        return $middlewareQueue;
    }

    public function addMiddleware(MiddlewareInterface $middleware): \GPDCore\Application\Core\Application
    {
        $this->middlewareQueue->add($middleware);

        return $this;
    }

    protected function createContext(): AppContextInterface
    {
        $context = \GPDCore\Application\Internal\AppContext::create(
            $this->config,
            $this->entityManager,
            $this->serviceManager,
            $this->enviroment
        );

        return $context;
    }

    public function getSchemaManager(): SchemaManager
    {
        return $this->schemaManager;
    }

    public function getTypesManager(): TypesManager
    {
        return $this->typesManager;
    }

    public function getResolverManager(): ResolverManagerInterface
    {
        return $this->resolverManager;
    }

    private function setApplicationToModules(\GPDCore\Application\Core\Application $application): void
    {
        foreach ($this->modules as $module) {
            $module->setApplication($this);
        }
    }

    private function registerModulesConfig(AppContextInterface $context): void
    {
        $config = $context->getConfig();
        foreach ($this->modules as $module) {
            $module->registerConfig($config, $context);
        }
    }

    private function registerModulesServices(AppContextInterface $context): void
    {
        $serviceManager = $context->getServiceManager();
        foreach ($this->modules as $module) {
            $module->registerServices($serviceManager, $context);
        }
    }

    private function registerModulesMiddleware(MiddlewareQueue $middlewareQueue, AppContextInterface $context): void
    {
        foreach ($this->modules as $module) {
            $module->registerMiddleware($middlewareQueue, $context);
        }
    }

    private function registerModulesRoutesAndGraphQLConfig(AppContextInterface $context): void
    {
        foreach ($this->modules as $module) {
            $module->registerType($this->typesManager, $context);
            $module->registerSchemaChunk($this->schemaManager, $context);
            $module->registerResolvers($this->resolverManager, $context);
            $module->registerRoutes($this->router, $context);
        }
    }

    private function createTypeManager(): TypesManager
    {
        $typesManager = new TypesManager();

        return $typesManager;
    }
}
