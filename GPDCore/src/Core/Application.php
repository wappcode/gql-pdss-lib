<?php

declare(strict_types=1);

namespace GPDCore\Core;

use AppModule\Services\AppRouter;
use Doctrine\ORM\EntityManager;
use Exception;
use GPDCore\Contracts\AppConfigInterface;
use GPDCore\Contracts\AppContextInterface;
use GPDCore\Contracts\ResolverManagerInterface;
use GPDCore\Graphql\Types\DateTimeType;
use GPDCore\Graphql\Types\DateType;
use GPDCore\Graphql\Types\JSONData;
use GPDCore\Routing\AbstractRouter;
use Laminas\ServiceManager\ServiceManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

class Application
{
    /**
     * Modulos de la aplicación.
     *
     * @var array<AbstractModule>
     */
    protected array $modules = [];

    protected AbstractRouter $router;

    protected bool $started = false;

    protected bool $productionMode = false;

    protected AppConfigInterface $config;

    protected AppContextInterface $context;

    protected SchemaManager $schemaManager;

    protected TypesManager $typesManager;

    protected ResolverManagerInterface $resolverManager;

    protected string $enviroment;

    protected ?EntityManager $entityManager;

    /**
     * Al establecer el valor la cadena deberá iniciar con /
     * ejemplo /micarpeta/public.
     */
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

    /**
     * El último módulo agregado debe ser el modulo de la app pricipal para que sobreescriba la configuración de los demás modulos.
     */
    public function addModule(string|AbstractModule $module): Application
    {
        if ($this->started) {
            throw new Exception('Solo se puede asignar los módulos antes de que la aplicación inicie');
        }
        if (is_string($module)) {
            /** @var AbstractModule */
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
        $this->withContextAttribute(Application::class, $this);
        // Se registra las diferentes partes de un módulo por separado para evitar que el orden de registro afecte, por ejemplo, que un módulo necesite registrar servicios para luego registrar los resolvers que los utilizan
        // Esto tambien permite que se sobreescriban los servicios y así todos usan la misma instancia que sería la última que se registro evitando que un modulo utilice una y cuando otro modulo la sobreescribe utiliza otra.
        $this->setApplicationToModules($this);
        $this->registerModulesConfig($this->context);
        $this->registerModulesServices($this->context);
        $this->registerModulesMiddleware($this->middlewareQueue, $this->context);
        $this->registerModulesGraphQLConfig($this->context);

        // Ejecuta la cola de middlewares FrameworkHandler y ese a su vez ejecuta $app->dispatch() de la aplicación
        $this->request = $request;
        $this->request = $request->withAttribute(Application::class, $this);
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

    /**
     * Posibles valores (production, development, testing).
     */
    public function getEnviroment(): string
    {
        return $this->enviroment;
    }

    public function getBaseHref(): string
    {
        return $this->baseHref;
    }

    /**
     * Get the value of middlewareQueue.
     */
    protected function createMiddlewareQueue(): MiddlewareQueue
    {
        $frameworkHandler = new FrameworkHandler($this);
        $middlewareQueue = new MiddlewareQueue($frameworkHandler);

        return $middlewareQueue;
    }

    public function addMiddleware(MiddlewareInterface $middleware): Application
    {
        $this->middlewareQueue->add($middleware);

        return $this;
    }

    protected function createContext(): AppContextInterface
    {
        $context = AppContext::create(
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

    private function setApplicationToModules(Application $application): void
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
    private function registerModulesGraphQLConfig(AppContextInterface $context): void
    {
        foreach ($this->modules as $module) {
            $module->registerType($this->typesManager, $context);
            $module->registerSchemaChunk($this->schemaManager, $context);
            $module->registerResolvers($this->resolverManager, $context);
        }
    }
    /**
     * Inicializa y agrega los tipos scalar basicos de la librería
     *
     * @return TypesManager
     */
    private function createTypeManager(): TypesManager
    {
        $typesManager = new TypesManager();
        $typesManager->add(DateType::NAME, DateType::class);
        $typesManager->add(DateTimeType::NAME, DateTimeType::class);
        $typesManager->add(JSONData::NAME, JSONData::class);
        return $typesManager;
    }
}
