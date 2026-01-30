<?php

use AppModule\AppWithoutDoctrineModule;
use AppModule\Services\AppRouter;
use GPDCore\Core\AppConfig;
use GPDCore\Core\Application;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\ServiceManager\ServiceManager;

require_once __DIR__ . '/../../vendor/autoload.php';
$masterConfig = require __DIR__ . '/../config/master.config.php';
$config = AppConfig::getInstance()->setMasterConfig($masterConfig);
$request = ServerRequestFactory::fromGlobals();
$enviroment = getenv('APP_ENV');
$serviceManager = new ServiceManager();
$router = new AppRouter();
$app = new Application($config, entityManager: null, enviroment: $enviroment);
$response = $app
    ->addModule(AppWithoutDoctrineModule::class)
    ->run($request);
$emitter = new SapiEmitter();
$emitter->emit($response);
