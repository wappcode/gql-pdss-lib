<?php

use AppModule\AppWithoutDoctrineModule;
use AppModule\Services\AppRouter;
use GPDCore\Library\GPDApp;
use GPDCore\Services\ContextService;
use Laminas\ServiceManager\ServiceManager;

require_once __DIR__ . '/../../vendor/autoload.php';
$cacheDir = __DIR__ . '/../data/DoctrineORMModule';
$enviroment = getenv('APP_ENV');
$serviceManager = new ServiceManager();
$context = new ContextService($serviceManager);
$router = new AppRouter();
$app = new GPDApp($context, $router, $enviroment, $withoutDoctrine = true);
$app->addModules([
    AppWithoutDoctrineModule::class,
]);
$localConfig = require __DIR__ . '/../config/local.config.php';
$context->getConfig()->add($localConfig);
$app->run();
