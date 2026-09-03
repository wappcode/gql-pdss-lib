<?php

declare(strict_types=1);

namespace GPDCore\Application\Graphql;

use GPDCore\Application\Contracts\ResolverMiddlewareInterface;
use GPDCore\Application\Graphql\ResolverPipeline;
use GPDCore\Application\Graphql\ResolverWrapperMiddleware;

final class ResolverPipelineFactory
{
    public static function createPipeline(callable $resolve, array $middlewares): ResolverPipeline
    {
        $queue = new ResolverPipeline($resolve);
        foreach ($middlewares as $middleware) {
            if (!($middleware instanceof ResolverMiddlewareInterface)) {
                throw new \InvalidArgumentException('Middleware must implement ResolverMiddlewareInterface');
            }
            $queue->pipe($middleware);
        }
        return $queue;
    }

    public static function createWrapper(callable $proxy): ResolverWrapperMiddleware
    {
        return new ResolverWrapperMiddleware($proxy);
    }
}
