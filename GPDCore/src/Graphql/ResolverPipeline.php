<?php

declare(strict_types=1);

namespace GPDCore\Graphql;

use Closure;
use GPDCore\Contracts\ResolverMiddlewareInterface;
use GPDCore\Contracts\ResolverPipelineInterface;
use GPDCore\Contracts\ResolverPipelineHandlerInterface;

final class ResolverPipeline implements
    ResolverPipelineInterface,
    ResolverPipelineHandlerInterface
{
    /** @var ResolverMiddlewareInterface[] */
    private array $queue = [];

    private int $index = 0;

    private Closure $baseResolver;

    public function __construct(callable $baseResolver)
    {
        $this->baseResolver = Closure::fromCallable($baseResolver);
    }

    public function pipe(ResolverMiddlewareInterface $middleware): void
    {
        $this->queue[] = $middleware;
    }

    /**
     * API pública
     * Devuelve el resolver final
     */
    public function build(): callable
    {
        $this->index = 0;
        return $this->handle($this->baseResolver);
    }
    /**
     * Infraestructura interna (next)
     */
    public function handle(callable $resolver): callable
    {
        if (!isset($this->queue[$this->index])) {
            return $resolver;
        }

        $middleware = $this->queue[$this->index];
        ++$this->index;

        return $middleware->wrap($resolver, $this);
    }
}
