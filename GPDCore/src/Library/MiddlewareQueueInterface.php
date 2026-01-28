<?php

namespace GPDCore\Library;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

interface MiddlewareQueueInterface
{
    public function add(MiddlewareInterface $middleware): void;

    public function handle(ServerRequestInterface $request): ResponseInterface;
}
