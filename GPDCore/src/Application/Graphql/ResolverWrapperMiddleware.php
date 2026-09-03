<?php

declare(strict_types=1);

namespace GPDCore\Application\Graphql;

use Closure;
use GPDCore\Application\Contracts\ResolverMiddlewareInterface;
use GPDCore\Application\Contracts\ResolverPipelineHandlerInterface;

class ResolverWrapperMiddleware implements ResolverMiddlewareInterface
{
    private Closure $wrapper;

    public function __construct(callable $wrapper)
    {
        $this->wrapper = Closure::fromCallable($wrapper);
    }

    public function wrap(callable $resolve, ResolverPipelineHandlerInterface $handler): callable
    {
        $resolver = ($this->wrapper)($resolve);
        return $handler->handle($resolver);
    }
}
