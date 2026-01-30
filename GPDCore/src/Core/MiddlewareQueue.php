<?php

namespace GPDCore\Core;

use GPDCore\Contracts\MiddlewareQueueInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class MiddlewareQueue implements MiddlewareQueueInterface, RequestHandlerInterface
{
    /** @var MiddlewareInterface[] */
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
            // no hay más middleware → delega al handler final
            return $this->finalHandler->handle($request);
        }

        $middleware = $this->queue[$this->index];
        ++$this->index;

        return $middleware->process($request, $this);
    }
}
