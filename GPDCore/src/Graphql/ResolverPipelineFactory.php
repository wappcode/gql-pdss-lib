<?php

namespace GPDCore\Graphql;

use GPDCore\Contracts\ResolverMiddlewareInterface;

final class ResolverPipelineFactory
{

    /**
     * Crea y maneja una cola de middlewares para la resolución GraphQL
     *
     * @param callable $resolver
     * @param array<ResolverMiddlewareInterface> $middlewares
     * @return ResolverPipeline
     */
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
