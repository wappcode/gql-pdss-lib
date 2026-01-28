<?php

namespace GPDCore\Library;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class FrameworkHandler implements RequestHandlerInterface
{

    private  GPDApp $app;
    public function __construct(GPDApp $app)
    {
        $this->app = $app;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->app->dispatch();
    }
}
