<?php

use AppModule\AppModule;
use AppModule\Services\AppRouter;
use GPDCore\Library\GPDApp;
use GPDCore\Library\AppContext;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\ServiceManager\ServiceManager;

require_once __DIR__ . '/../../vendor/autoload.php';
$configFile = __DIR__ . '/../config/doctrine.local.php';
$cacheDir = __DIR__ . '/../data/DoctrineORMModule';
$enviroment = getenv('APP_ENV');
$serviceManager = new ServiceManager();
$context = AppContext::create(serviceManager: $serviceManager);
$context->setDoctrineConfigFile($configFile);
$context->setDoctrineCacheDir($cacheDir);
$router = new AppRouter();
$app = new GPDApp($context, $router, $enviroment);
$app->addModule(
    AppModule::class
);
$localConfig = require __DIR__ . '/../config/local.config.php';
$context->getConfig()->add($localConfig);
$request = ServerRequestFactory::fromGlobals();
$response = $app->run($request);
$emitter = new SapiEmitter();
$emitter->emit($response);
