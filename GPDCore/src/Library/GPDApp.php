<?php

declare(strict_types=1);

namespace GPDCore\Library;

use AppModule\Services\AppRouter;
use Doctrine\ORM\EntityManager;
use Exception;
use Laminas\ServiceManager\ServiceManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

class GPDApp
{
    /**
     * Modulos de la aplicación
     *
     * @var array<AbstractModule>
     */
    protected $modules = [];
    protected AbstractRouter $router;
    protected $started = false;
    protected $productionMode = false;
    protected AppConfigInterface $config;
    protected AppContextInterface $context;
    protected SchemaManager $schemaManager;
    protected TypesManager $typesManager;
    protected ResolverManagerInterface $resolverManager;
    protected $enviroment;
    protected $servicesAndGQLTypes = [];
    protected ?EntityManager $entityManager;
    /**
     * Al establecer el valor la cadena deberá iniciar con /
     * ejemplo /micarpeta/public.
     * @var string
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
        $this->typesManager = new TypesManager();
        $this->schemaManager = new SchemaManager();
        $this->router = new AppRouter();
        $this->middlewareQueue = $this->createMiddlewareQueue();
        $this->productionMode = $enviroment === AppContextInterface::ENV_PRODUCTION;
    }
    /**
     * El último módulo agregado debe ser el modulo de la app pricipal para que sobreescriba la configuración de los demás modulos.
     */
    public function addModule(string | AbstractModule $module): GPDApp
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



    public function run(ServerRequestInterface $request)
    {

        $this->applyProductionMode();
        $this->context = $this->createContext();
        $this->registerModules();
        $this->context = $this->context->withContextAttribute(GPDApp::class, $this);
        $this->request = $request->withAttribute(AppContextInterface::class, $this->context);
        $this->request = $request->withAttribute(GPDApp::class, $this);
        // Ejecuta la cola de middlewares FrameworkHandler y ese a su vez ejecuta $app->dispatch() de la aplicación
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
        if (!$this->productionMode) {
            return;
        }

        if ($this->productionMode) {
            ini_set('display_errors', '0');
            error_reporting(0);
        } else {
            ini_set('display_errors', '1');
        }

        return;
    }

    /**
     * Posibles valores (production, development, testing).
     */
    public function getEnviroment()
    {
        return $this->enviroment;
    }


    public function getBaseHref()
    {
        return $this->baseHref;
    }

    /**
     * Get the value of middlewareQueue
     */
    protected function createMiddlewareQueue(): MiddlewareQueue
    {
        $frameworkHandler = new FrameworkHandler($this);
        $middlewareQueue = new MiddlewareQueue($frameworkHandler);
        return $middlewareQueue;
    }

    public function adMiddleware(MiddlewareInterface $middleware): GPDApp
    {
        $this->middlewareQueue->add($middleware);
        return $this;
    }

    protected function  createContext(): AppContextInterface
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

    private function registerModules()
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
}
