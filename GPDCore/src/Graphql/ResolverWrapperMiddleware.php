<?php

namespace GPDCore\Graphql;

use Closure;
use GPDCore\Contracts\ResolverMiddlewareInterface;
use GPDCore\Contracts\ResolverPipelineHandlerInterface;

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
