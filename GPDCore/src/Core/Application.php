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

    public function run(ServerRequestInterface $request): ResponseInterface
    {
        $this->applyProductionMode();
        $this->context = $this->createContext();
        $this->context = $this->context->withContextAttribute(Application::class, $this);
        $this->registerModules();
        // Ejecuta la cola de middlewares FrameworkHandler y ese a su vez ejecuta $app->dispatch() de la aplicación
        $this->request = $request;
        $this->request = $request->withAttribute(AppContextInterface::class, $this->context);
        $this->request = $request->withAttribute(Application::class, $this);
        $response = $this->middlewareQueue->handle($this->request);

        return $response;
    }

    public function dispatch(): ResponseInterface
    {
        $this->started = true;

        return $this->router->dispatch($this->request);
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

    private function registerModules(): void
    {
        foreach ($this->modules as $module) {
            $module->registerModule(
                $this->schemaManager,
                $this->resolverManager,
                $this->middlewareQueue,
                $this->typesManager,
                $this->config,
                $this->context,
                $this->serviceManager,
            );
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
