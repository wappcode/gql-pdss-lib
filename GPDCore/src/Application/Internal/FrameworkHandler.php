<?php

declare(strict_types=1);

namespace GPDCore\Application\Internal;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use GPDCore\Application\Core\Application;

final class FrameworkHandler implements RequestHandlerInterface
{
    private Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->app->dispatch($request);
    }
}
