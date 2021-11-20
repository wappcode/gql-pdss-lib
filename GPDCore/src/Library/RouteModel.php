<?php

namespace GPDCore\Library;

class RouteModel {
    protected $method;

    protected $route;

    protected $contoller;

    /**
     * Constructor RouteModel
     *
     * @param mixed string | string[] (Ejemplo: 'GET' o ['GET', 'POST', ...])
     * @param string $route
     * @param mixed $contoller string || callable
     */
    public function __construct($method, string $route,  $contoller)
    {
        $this->method = $method;
        $this->route = $route;
        $this->contoller = $contoller;
    }

    

    /**
     * Get the value of method
     */ 
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set the value of method
     *
     * @return  self
     */ 
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Get the value of route
     */ 
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set the value of route
     *
     * @return  self
     */ 
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Get the value of contoller
     */ 
    public function getContoller()
    {
        return $this->contoller;
    }

    /**
     * Set the value of contoller
     *
     * @return  self
     */ 
    public function setContoller($contoller)
    {
        $this->contoller = $contoller;

        return $this;
    }
}