<?php

namespace GPDCore\Routing;

use Exception;
use FastRoute;
use GPDCore\Contracts\AppControllerInterface;
use GPDCore\Core\Application;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\StreamFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function FastRoute\simpleDispatcher;

abstract class AbstractRouter implements RouterInterface
{
    protected $routes = [];

    protected $isProductionMode;

    protected ResponseFactory $responseFactory;

    protected StreamFactory $streamFactory;

    public function __construct()
    {
        $this->responseFactory = new ResponseFactory();
        $this->streamFactory = new StreamFactory();
    }

    public function add(RouteModel $route)
    {
        array_push($this->routes, $route);
    }

    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
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
                $routeParams = $routeInfo[2];

                try {
                    if ($handler instanceof AppControllerInterface) {
                        $response = $handler->dispatch($request);
                        $handler->setRouteParams($routeParams);
                    } elseif (is_callable($handler)) {
                        $response = $handler($request);
                    } elseif (is_string($handler) && class_exists($handler)) {
                        /** @var AppControllerInterface */
                        $controller = new $handler();
                        if (!$controller instanceof AppControllerInterface) {
                            throw new Exception('El controlador de la ruta debe implementar AppControllerInterface');
                        }
                        $response = $controller->dispatch($request);
                        $controller->setRouteParams($routeParams);
                    } else {
                        throw new Exception('Controlador de ruta no válido');
                    }
                    if (!($response instanceof ResponseInterface)) {
                        throw new Exception('El controlador de la ruta debe retornar una instancia de ResponseInterface');
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

        $app = $request->getAttribute(Application::class);

        // Usar SCRIPT_NAME (más estándar que SCRIPT_FILENAME)
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        if ($scriptName && $scriptName !== '/') {
            // Remover el script name del inicio de la ruta (solo la primera ocurrencia)
            $path = preg_replace('~^' . preg_quote($scriptName) . '~', '', $path);
        }
        // Remueve el base href si existe
        $baseHref = $app->getBaseHref() ?? '';
        if ($baseHref && $baseHref !== '/') {
            $path = preg_replace('~^' . preg_quote($baseHref) . '~', '', $path);
        }

        return $path ?: '/';
    }
}
