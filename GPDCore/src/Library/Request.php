<?php

namespace GPDCore\Library;

class Request
{
    protected $method;

    protected $content;

    protected $routeParams;

    protected $queryParams;

    protected $params;

    public function __construct(string $method, ?array $routeParams, ?array $content, ?array $queryParams)
    {
        $this->method = $method;
        $this->content = $content;
        $this->routeParams = $routeParams;
        $this->queryParams = $queryParams;
        $this->params = array_merge($routeParams, $queryParams);
    }

    /**
     * Get the value of method.
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Get the value of content.
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get the value of routeParams.
     */
    public function getRouteParams()
    {
        return $this->routeParams;
    }

    /**
     * Get the value of queryParams.
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    public function getParam($key, $default = null)
    {
        return $this->params[$key] ?? $default;
    }

    public function setParam($key, $value)
    {
        $this->params[$key] = $value;
    }
}
