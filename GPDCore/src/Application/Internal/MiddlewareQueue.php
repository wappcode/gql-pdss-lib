<?php

declare(strict_types=1);

namespace GPDCore\Application\Internal;

use GPDCore\Application\Contracts\MiddlewareQueueInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class MiddlewareQueue implements MiddlewareQueueInterface, RequestHandlerInterface
{
    private array $queue = [];
    private ?RequestHandlerInterface $finalHandler = null;
    private int $index = 0;

    public function __construct(RequestHandlerInterface $finalHandler)
    {
        $this->finalHandler = $finalHandler;
    }

    public function add(MiddlewareInterface $middleware): void
    {
        $this->queue[] = $middleware;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!isset($this->queue[$this->index])) {
            return $this->finalHandler->handle($request);
        }

        $middleware = $this->queue[$this->index];
        ++$this->index;

        return $middleware->process($request, $this);
    }
}
