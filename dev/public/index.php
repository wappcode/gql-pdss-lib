<?php

use AppModule\AppModule;
use AppModule\Services\AppRouter;
use GPDCore\Factory\EntityManagerFactory;
use GPDCore\Library\AppConfig;
use GPDCore\Library\GPDApp;
use GPDCore\Library\AppContext;
use GPDCore\Library\AppContextInterface;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\ServiceManager\ServiceManager;

require_once __DIR__ . '/../../vendor/autoload.php';
$configFile = __DIR__ . '/../config/doctrine.local.php';
$cacheDir = __DIR__ . '/../data/DoctrineORMModule';
$enviroment = getenv('APP_ENV');
$serviceManager = new ServiceManager();
$masterConfig = require __DIR__ . '/../config/master.config.php';
$config = AppConfig::getInstance()->setMasterConfig($masterConfig);
$entityManagerOptions = $options = file_exists($configFile) ? require $configFile : [];
$isEntityManagerDevMode = $enviroment !== AppContextInterface::ENV_PRODUCTION;
$entityManager = EntityManagerFactory::createInstance($options, $cacheDir, $isEntityManagerDevMode);
$request = ServerRequestFactory::fromGlobals();
$context = AppContext::create($config, $request, $entityManager, $serviceManager,  $enviroment);
$router = new AppRouter();
$app = new GPDApp($context, $router, $enviroment);
$app->addModule(
    AppModule::class
);
//Poner la instancia de la app en el contexto para que los controladores y servicios puedan acceder a ella
$context = $context->withContextAttribute(GPDApp::class, $app);

$response = $app->run($request);
$emitter = new SapiEmitter();
$emitter->emit($response);
