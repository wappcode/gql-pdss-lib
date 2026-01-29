<?php


namespace GPDCore\Library;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface AppControllerInterface
{
    public function dispatch(ServerRequestInterface $request): ResponseInterface;
    public function setRouteParams(?array $params): void;
}
