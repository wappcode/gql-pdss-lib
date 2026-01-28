<?php

namespace GPDCore\Library;

use Exception;
use FastRoute;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\StreamFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function FastRoute\simpleDispatcher;

abstract class AbstractRouter
{
    protected $routes = [];

    protected $isProductionMode;

    /**
     * @var AppContextInterface
     */
    protected $context;

    /**
     * @var GPDApp
     */
    protected $app;

    protected ResponseFactory $responseFactory;
    protected StreamFactory $streamFactory;

    public function __construct()
    {
        $this->responseFactory = new ResponseFactory();
        $this->streamFactory = new StreamFactory();
    }

    public function setApp(GPDApp $app)
    {
        if ($this->app instanceof GPDApp) {
            throw new Exception('Solo se puede establecer el valor de app una vez');
        }
        $this->app = $app;
        $this->isProductionMode = $this->app->getProductionMode();
        $this->context = $this->app->getContext();
    }

    abstract protected function addRoutes();

    protected function addRoute(RouteModel $route)
    {
        array_push($this->routes, $route);
    }

    public function dispatch(): ResponseInterface
    {
        $request = ServerRequestFactory::fromGlobals();

        if ($request->getMethod() === 'OPTIONS') {
            $response = $this->responseFactory->createResponse(200)
                ->withHeader('Content-Type', 'application/json; charset=UTF-8');
            $response->getBody()->write('{}');
            return $response;
        }

        $dispatcher = $this->createDispatcher();
        $httpMethod = $request->getMethod();
        $uri = $this->getUriFromRequest($request);

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);

        switch ($routeInfo[0]) {
            case FastRoute\Dispatcher::NOT_FOUND:
                $response = $this->responseFactory->createResponse(404);
                return $response;

            case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                $response = $this->responseFactory->createResponse(405)
                    ->withHeader('Allow', implode(', ', $allowedMethods));
                return $response;

            case FastRoute\Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];

                // Añadir variables de ruta a los atributos del request
                foreach ($vars as $key => $value) {
                    $request = $request->withAttribute($key, $value);
                }

                try {
                    if (is_callable($handler)) {
                        $response = $handler($request);
                    } else {
                        /** @var AbstractAppController */
                        $controller = new $handler($request, $this->app);
                        $response = $controller->dispatch();
                    }
                    return $response;
                } catch (Exception $e) {
                    $code = $e->getCode() ?: 500;
                    $msg = $e->getMessage();
                    $response = $this->responseFactory->createResponse($code);
                    $response->getBody()->write($msg);
                    return $response;
                }

            default:
                $response = $this->responseFactory->createResponse(500);
                $response->getBody()->write('Routing error');
                return $response;
        }
    }

    protected function createDispatcher()
    {
        $this->addRoutes();
        $dispatcher = simpleDispatcher(function (FastRoute\RouteCollector $router) {
            foreach ($this->routes as $route) {
                $router->addRoute($route->getMethod(), $route->getRoute(), $route->getContoller());
            }
        });

        return $dispatcher;
    }

    protected function getUriFromRequest(ServerRequestInterface $request): string
    {
        $path = $request->getUri()->getPath();

        // Usar SCRIPT_NAME (más estándar que SCRIPT_FILENAME)
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        if ($scriptName && $scriptName !== '/') {
            // Remover el script name del inicio de la ruta (solo la primera ocurrencia)
            $path = preg_replace('~^' . preg_quote($scriptName) . '~', '', $path);
        }
        // Remueve el base href si existe
        $baseHref = $this->app->getBaseHref();
        if ($baseHref && $baseHref !== '/') {
            $path = preg_replace('~^' . preg_quote($baseHref) . '~', '', $path);
        }

        return $path ?: '/';
    }
}
