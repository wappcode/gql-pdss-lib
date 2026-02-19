<?php

use AppModule\AppModule;
use GPDCore\Contracts\AppContextInterface;
use GPDCore\Core\AppConfig;
use GPDCore\Core\Application;
use GPDCore\Factory\EntityManagerFactory;
use GraphqlModule\GraphqlModule;
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
$app = new Application($config, $entityManager, $enviroment);
$app
    ->addModule(new GraphqlModule(route: '/api'))
    ->addModule(AppModule::class);

$response = $app->run($request);
$emitter = new SapiEmitter();
$emitter->emit($response);
