<?php

declare(strict_types=1);

namespace GPDCore\Library;

use GPDCore\Library\AbstractModule;
use GPDCore\Library\AbstractRouter;
use GPDCore\Services\ConfigService;
use Exception;
use GPDCore\Library\IContextService;

class GPDApp
{

    const ENVIROMENT_PRODUCTION = 'production';
    const ENVIROMENT_DEVELOPMENT = 'development';
    const ENVIROMENT_TESTING = 'testing';

    private $modules = [];
    private $router;
    private $started = false;
    private $productionMode = false;
    private $context;
    private $enviroment;

    public function __construct(IContextService $context, AbstractRouter $router, ?string $enviroment)
    {
        $enviroment = empty($enviroment) ? GPDApp::ENVIROMENT_DEVELOPMENT : $enviroment;
        $this->enviroment = trim(strtolower($enviroment));
        $productionMode = $this->enviroment === trim(strtolower(GPDApp::ENVIROMENT_PRODUCTION));
        $this->setProductionMode($productionMode);
        $this->setContext($context);
        $this->setRouter($router);
    }


    /**
     * Los modulos se agregan en orden inverso para que el primer modulo registrado (App) sobreescriba la configuración de los demás modulos
     *
     * @param array $modules
     * @return GPDApp
     */
    public function addModules(array $modules): GPDApp
    {
        if ($this->started) {
            throw new Exception('Solo se puede asignar los módulos antes de que la aplicación inicie');
        }
        $modulesList = [];
        foreach ($modules as $moduleClass) {
            /**@var AbstractModule */
            $module = new $moduleClass($this);
            array_push($modulesList, $module);
        }
        $this->modules = $modulesList;
        $this->addConfig();
        $this->addServices();


        return $this;
    }
    public function getModules(): array
    {
        return $this->modules;
    }
    public function getContext(): IContextService
    {
        return $this->context;
    }

    public function getProductionMode(): bool
    {
        return $this->productionMode;
    }
    public function run()
    {
        $this->started = true;
        $this->router->dispatch();
    }

    protected function setContext(IContextService $context)
    {
        if ($this->started) {
            throw new Exception('Solo se puede asignar el contexto antes de que la aplicación inicie');
        }
        $this->context = $context;
        $this->context->init($this->enviroment, $this->productionMode);
        return $this;
    }

    protected function setProductionMode(bool $productionMode)
    {
        $this->productionMode = $productionMode;
        if ($this->productionMode) {
            ini_set("display_errors", '0');
            error_reporting(0);
        } else {
            ini_set("display_errors", '1');
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
     * Agrega la configuración de los módulos al servicio config
     *
     * @return void
     */
    protected function addConfig()
    {
        /**@var AbstractModule */
        foreach ($this->modules as $module) {
            $config = $module->getConfig();
            $configService = $this->context->getConfig();
            $configService->add($config);
        }
    }

    /**
     * Agrega los servicios de los módulos 
     *
     * @return void
     */
    protected function addServices()
    {
        /** @var AbstractModule */
        foreach ($this->modules as $module) {
            $services = $module->getServicesAndGQLTypes();
            $this->addServicesAndGQLTypes($services);
        }
    }

    private function addServicesAndGQLTypes(array $services)
    {
        $factories = $services["factories"] ?? [];
        $invokables = $services["invokables"] ?? [];
        $aliases = $services["aliases"] ?? [];
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


        $selfInvokables = $this->servicesAndGQLTypes["invokables"] ?? [];
        $selfFactories = $this->servicesAndGQLTypes["factories"] ?? [];
        $selfAliases = $this->servicesAndGQLTypes["aliases"] ?? [];
        $this->servicesAndGQLTypes["invokables"] = array_merge($selfInvokables, $invokables);
        $this->servicesAndGQLTypes["factories"] = array_merge($selfFactories, $factories);
        $this->servicesAndGQLTypes["aliases"] = array_merge($selfAliases, $aliases);
    }

    /**
     * Posibles valores (production, development, testing)
     */
    public function getEnviroment()
    {
        return $this->enviroment;
    }
}
