<?php

namespace GPDCore\Library;

use Exception;
use FastRoute;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\StreamFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function FastRoute\simpleDispatcher;

abstract class AbstractRouter
{
    protected $routes = [];

    protected $isProductionMode;

    /**
     * @var IContextService
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

    public function dispatch()
    {
        $request = ServerRequestFactory::fromGlobals();

        if ($request->getMethod() === 'OPTIONS') {
            $response = $this->responseFactory->createResponse(200)
                ->withHeader('Content-Type', 'application/json; charset=UTF-8');
            $response->getBody()->write('{}');
            $this->emit($response);
            return;
        }

        $dispatcher = $this->createDispatcher();
        $httpMethod = $request->getMethod();
        $uri = $this->getUriFromRequest($request);

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);

        switch ($routeInfo[0]) {
            case FastRoute\Dispatcher::NOT_FOUND:
                $response = $this->responseFactory->createResponse(404);
                $this->emit($response);
                break;

            case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                $response = $this->responseFactory->createResponse(405)
                    ->withHeader('Allow', implode(', ', $allowedMethods));
                $this->emit($response);
                break;

            case FastRoute\Dispatcher::FOUND:
                try {
                    $handler = $routeInfo[1];
                    $vars = $routeInfo[2];

                    // Añadir variables de ruta a los atributos del request
                    foreach ($vars as $key => $value) {
                        $request = $request->withAttribute($key, $value);
                    }
                    if (is_callable($handler)) {
                        $controller = $handler();
                    } else {
                        $controller = new $handler($request, $this->app);
                    }

                    $controller->dispatch();
                } catch (Exception $e) {
                    $code = $e->getCode() ?: 500;
                    $msg = $e->getMessage();
                    $response = $this->responseFactory->createResponse($code);
                    $response->getBody()->write($msg);
                    $this->emit($response);
                }
                break;
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
        $uri = $request->getUri()->getPath();
        $scriptName = $this->getScriptName();
        $uri = str_replace($scriptName, '', $uri);

        $baseHref = $this->app->getBaseHref();
        $uri = str_replace($baseHref, '', $uri);

        return $uri;
    }

    protected function decodeParams(array $params): array
    {
        $decoded = [];
        foreach ($params as $k => $v) {
            $decoded[$k] = urldecode($v);
        }

        return $decoded;
    }

    protected function getScriptName(): string
    {
        $fileScript = $_SERVER['SCRIPT_FILENAME'];
        $start = strrpos($fileScript, '/');
        $scriptName = substr($fileScript, $start);

        return $scriptName;
    }

    protected function emit(ResponseInterface $response): void
    {
        $emitter = new SapiEmitter();
        $emitter->emit($response);
    }
}
