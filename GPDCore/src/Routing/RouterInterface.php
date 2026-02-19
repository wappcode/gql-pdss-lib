<?php

namespace GPDCore\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface RouterInterface
 * 
 * Define el contrato que debe cumplir cualquier router en el sistema.
 * Los routers son responsables de manejar las peticiones HTTP entrantes,
 * determinar qué controlador debe manejar cada ruta y devolver una respuesta apropiada.
 */
interface RouterInterface
{
    /**
     * Despacha una petición HTTP a su controlador correspondiente
     * 
     * @param ServerRequestInterface $request La petición HTTP entrante
     * @return ResponseInterface La respuesta HTTP generada
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface;

    /**
     * Agrega una ruta al router
     * 
     * @param RouteModel $route El modelo de ruta a agregar
     * @return void
     */
    public function add(RouteModel $route);
}
