<?php

namespace GPDCore\Library;

use Exception;
use FastRoute;
use GPDCore\Library\Request;
use GPDCore\Library\RouteModel;
use function FastRoute\simpleDispatcher;

abstract class AbstractRouter
{

    
    protected $routes = [];
    protected $isProductionMode;

    public function __construct(bool $isProductionMode)
    {
        $this->isProductionMode = $isProductionMode;
    }   

    protected abstract function addRoutes();


    protected function addRoute(RouteModel $route) {
        array_push($this->routes, $route);
    }

    public function dispatch()
    {
        // Fetch method and URI from somewhere
        $httpMethod = $this->getMethod();
        $uri = $this->getUriBase();

        if($httpMethod === 'OPTIONS') {
            header("Content-Type: application/json; charset=UTF-8");
            echo "{}";
                exit;
        }
        $dispatcher = $this->createDispatcher();
        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
        switch ($routeInfo[0]) {
            case FastRoute\dispatcher::NOT_FOUND:
                header("HTTP/1.0 404 Not Found");
                exit;
                break;
            case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                header("HTTP/1.0 404 Method not allowed");
                exit;
                break;
            case FastRoute\Dispatcher::FOUND:
                try {
                    $handler = $routeInfo[1];
                    $vars = $routeInfo[2];
                    $request = $this->getRequest($vars);
                    $controler = new $handler($request);
                    $controler->dispatch();
                }catch(Exception $e) {
                    $code = $e->getCode() ?? 500;
                    $msg = $e->getMessage();
                    header("HTTP/1.0 {$code} {$msg}");
                    echo $msg;
                }
                

                break;
        }
    }

    protected function createDispatcher() {
        $this->addRoutes();
        $dispatcher = simpleDispatcher(function (FastRoute\RouteCollector $router) {
            /**@var RouteModel */
            foreach($this->routes as $route) {
                $router->addRoute($route->getMethod(), $route->getRoute(), $route->getContoller());
            }
        });
        return $dispatcher;
    }
    protected function getRequest($routeParams) {
        $content = $this->getRequestData();
        $queryParams = $this->getQueryParams();
        $queryParams = $this->getQueryParams();
        $decodedQueryParams = $this->decodeParams($queryParams);
        $method = $this->getMethod();
        $request = new Request($method, $routeParams, $content, $decodedQueryParams);
        return $request;
    }
    protected function getMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }
    protected function getRequestData() {
        $rawInput = file_get_contents("php://input");
        return json_decode($rawInput, true);
        
    }

    protected function getUriBase() {
        
        $uri = $_SERVER['REQUEST_URI'];
        $scriptName = $this->getScriptName();
        $uri = str_replace($scriptName, '', $uri);
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        return rawurldecode($uri);
    }

    protected function getQueryParams() {;
        $uri = $_SERVER['REQUEST_URI'];
        $scriptName = $_SERVER["SCRIPT_NAME"];
        $uri = str_replace($scriptName, '', $uri);
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, ($pos+1));
            $params = explode('&', $uri);
            $result = array();
            foreach($params as $item) {
                $itemData = explode('=', $item);
                if(!empty($itemData[0])) {
                    $result[$itemData[0]] = isset($itemData[1]) ? $itemData[1] : '';
                }
            }
        } else {
            $result = [];
        }
       
    
        return $result;
    }
    protected function decodeParams(array $params) {
        $decoded = [];
        foreach($params as $k => $v) {
            $decoded[$k] = urldecode($v);
        }
        return $decoded;
    }

    protected function getScriptName() {
        $fileScript = $_SERVER['SCRIPT_FILENAME'];
        $start = strrpos($fileScript, '/');
        $scriptName = substr($fileScript, $start);
        return $scriptName;
    }
    
}
