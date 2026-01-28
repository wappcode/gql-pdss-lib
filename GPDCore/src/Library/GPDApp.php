<?php

declare(strict_types=1);

namespace GPDCore\Library;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

class GPDApp
{
    public const ENVIROMENT_PRODUCTION = 'production';
    public const ENVIROMENT_DEVELOPMENT = 'development';
    public const ENVIROMENT_TESTING = 'testing';

    private $modules = [];

    private $router;

    private $started = false;

    private $productionMode = false;

    private $context;

    private $enviroment;

    protected $servicesAndGQLTypes = [];

    protected $withoutDoctrine = false;

    protected $baseHref = '';

    protected MiddlewareQueue $middlewareQueue;

    public function __construct(AppContextInterface $context, AbstractRouter $router, ?string $enviroment, bool $withoutDoctrine = false)
    {
        $this->withoutDoctrine = $withoutDoctrine;
        $enviroment = empty($enviroment) ? GPDApp::ENVIROMENT_DEVELOPMENT : $enviroment;
        $this->enviroment = trim(strtolower($enviroment));
        $productionMode = $this->enviroment === trim(strtolower(GPDApp::ENVIROMENT_PRODUCTION));
        $this->setProductionMode($productionMode);
        $this->setContext($context);
        $this->setRouter($router);
        $this->createMiddlewareQueue();
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

    public function getModules(): array
    {
        return $this->modules;
    }

    public function getContext(): AppContextInterface
    {
        return $this->context;
    }

    public function getProductionMode(): bool
    {
        return $this->productionMode;
    }

    public function run(ServerRequestInterface $request)
    {

        foreach ($this->modules as $module) {
            if ($module instanceof MiddlewareProviderInterface) {
                $module->registerMiddleware($this->middlewareQueue, $this->context);
            }
        }
        $request = $request->withAttribute(AppContextInterface::class, $this->context);

        $response = $this->middlewareQueue->handle($request);
        return $response;
    }
    public function dispatch(): ResponseInterface
    {
        $this->addConfig();
        $this->addServices();
        $this->started = true;

        //TODO: 
        // [ ] Agregar request al context 
        // [ ] Agregar app al context  ?

        return $this->router->dispatch();
    }

    protected function setContext(AppContextInterface $context)
    {
        if ($this->started) {
            throw new Exception('Solo se puede asignar el contexto antes de que la aplicación inicie');
        }
        $this->context = $context;
        $this->context->init($this->enviroment, $this->productionMode, $this->withoutDoctrine);

        return $this;
    }

    protected function setProductionMode(bool $productionMode)
    {
        $this->productionMode = $productionMode;
        if ($this->productionMode) {
            ini_set('display_errors', '0');
            error_reporting(0);
        } else {
            ini_set('display_errors', '1');
        }

        return $this;
    }

    protected function setRouter(AbstractRouter $router): GPDApp
    {
        if ($this->started) {
            throw new Exception('Solo se puede asignar el router antes de que la aplicación inicie');
        }
        $router->setApp($this);
        $this->router = $router;

        return $this;
    }

    /**
     * Agrega la configuración de los módulos al servicio config.
     */
    protected function addConfig()
    {
        // @var AbstractModule
        foreach ($this->modules as $module) {
            $config = $module->getConfig();
            $configService = $this->context->getConfig();
            $configService->add($config);
        }
    }

    /**
     * Agrega los servicios de los módulos.
     */
    protected function addServices()
    {
        // @var AbstractModule
        foreach ($this->modules as $module) {
            $services = $module->getServicesAndGQLTypes();
            $this->addServicesAndGQLTypes($services);
        }
    }

    private function addServicesAndGQLTypes(array $services)
    {
        $factories = $services['factories'] ?? [];
        $invokables = $services['invokables'] ?? [];
        $aliases = $services['aliases'] ?? [];
        $serviceManager = $this->context->getServiceManager();
        foreach ($invokables as $k => $invokable) {
            $serviceManager->setInvokableClass($k, $invokable);
        }
        foreach ($factories as $k => $factory) {
            $serviceManager->setFactory($k, $factory);
        }

        foreach ($aliases as $k => $alias) {
            $serviceManager->setAlias($k, $alias);
        }

        // TODO: Verificar si este código es necesario y si no lo es quitarlo
        $selfInvokables = $this->servicesAndGQLTypes['invokables'] ?? [];
        $selfFactories = $this->servicesAndGQLTypes['factories'] ?? [];
        $selfAliases = $this->servicesAndGQLTypes['aliases'] ?? [];
        $this->servicesAndGQLTypes['invokables'] = array_merge($selfInvokables, $invokables);
        $this->servicesAndGQLTypes['factories'] = array_merge($selfFactories, $factories);
        $this->servicesAndGQLTypes['aliases'] = array_merge($selfAliases, $aliases);
    }

    /**
     * Posibles valores (production, development, testing).
     */
    public function getEnviroment()
    {
        return $this->enviroment;
    }

    /**
     * Al establecer el valor la cadena deberá iniciar con /
     * ejemplo /micarpeta/public.
     */
    public function setBaseHref(string $baseHref)
    {
        $this->baseHref = $baseHref;
    }

    public function getBaseHref()
    {
        return $this->baseHref;
    }

    /**
     * Get the value of middlewareQueue
     */
    private function createMiddlewareQueue()
    {
        $frameworkHandler = new FrameworkHandler($this);
        $this->middlewareQueue = new MiddlewareQueue($frameworkHandler);
        return $this->middlewareQueue;
    }

    public function adMiddleware(MiddlewareInterface $middleware): GPDApp
    {
        $this->middlewareQueue->add($middleware);
        return $this;
    }
}
