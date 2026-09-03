<?php

declare(strict_types=1);

namespace GPDCore\Application\Core;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class FrameworkHandler implements RequestHandlerInterface
{
    private \GPDCore\Application\Core\Application $app;

    public function __construct(\GPDCore\Application\Core\Application $app)
    {
        $this->app = $app;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->app->dispatch($request);
    }
}
