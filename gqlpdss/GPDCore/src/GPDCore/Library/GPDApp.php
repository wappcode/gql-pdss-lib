<?php
declare(strict_types=1);

namespace GPDCore\Library;

use GPDCore\Library\AbstractModule;
use GPDCore\Library\AbstractRouter;
use GPDCore\Services\ConfigService;
use Exception;
use GPDCore\Library\IContextService;

class GPDApp {
    private static $instance;
    private $modules = [];
    private $router;
    private $started = false;
    private $productionMode = true;
    private $context;

    public static function getInstance(): GPDApp
    {

        if (static::$instance === null) {
            static::$instance = new GPDApp();
        }

        return static::$instance;
       
    }
    public function getModules(): array {
        return $this->modules;
    }
    /**
     * Los modulos se agregan en orden inverso para que el primer modulo registrado (App) sobreescriba la configuración de los demás modulos
     *
     * @param array $modules
     * @return GPDApp
     */
    public function setModules(array $modules): GPDApp {
        if($this->started) {
            throw new Exception('Solo se puede asignar los módulos antes de que la aplicación inicie');
        }
        $this->modules = $modules;
        $this->addConfig();
        
        return $this;
    }
    public function setRouter(AbstractRouter $router): GPDApp {
        if($this->started) {
            throw new Exception('Solo se puede asignar el router antes de que la aplicación inicie');
        }
        $this->router = $router;
        return $this;
    }
    public function getContext(): IContextService {
        return $this->context;
    }
    public function setContext(IContextService $context) {
        if($this->started) {
            throw new Exception('Solo se puede asignar el contexto antes de que la aplicación inicie');
        }
        $this->context = $context;
        return $this;
    }

    public function setProductionMode(bool $productionMode) {
        $this->productionMode = $productionMode;
        if ($this->productionMode) {
            ini_set("display_errors", '0');
            error_reporting(0);
        } else {
            ini_set("display_errors", '1');
            error_reporting(E_ALL);
        }
        return $this;
    }
    public function getProductionMode(): bool {
        return $this->productionMode;
    }
    public function run() {
        $this->started = true;
        $this->router->dispatch();
    }


    private function addConfig(){
        foreach($this->modules as $moduleClass) {
            /**@var AbstractModule */
            $module = new $moduleClass();
            $config = $module->getConfig();
            $service = ConfigService::getInstance();
            $service->add($config);
        }
    }

      

    /**
     * is not allowed to call from outside to prevent from creating multiple instances,
     * to use the singleton, you have to obtain the instance from Singleton::getInstance() instead
     */
    private function __construct() 
    {
    }

    /**
     * prevent the instance from being cloned (which would create a second instance of it)
     */
    private function __clone()
    {
    }

    /**
     * prevent from being unserialized (which would create a second instance of it)
     */
    private function __wakeup()
    {
    }
    
    
}