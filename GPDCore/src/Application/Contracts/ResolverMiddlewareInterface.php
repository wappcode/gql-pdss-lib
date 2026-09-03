<?php

declare(strict_types=1);

namespace GPDCore\Application\Contracts;


interface ResolverMiddlewareInterface
{
    /**
     * Acepta funcion con argumento resolve y devuelve una funcion resolve
     *
     * @param callable $resolve <fn($root, array $args, AppContextInterface $context, ResolveInfo $info): mixed>
     * @param ResolverPipelineHandlerInterface $handler
     * @return callable <fn($root, array $args, AppContextInterface $context, ResolveInfo $info): mixed>
     */
    public function wrap(callable $resolve, ResolverPipelineHandlerInterface $handler): callable;
}
