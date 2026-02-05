<?php

namespace GPDCore\Contracts;


interface ResolverPipelineHandlerInterface
{
    /**
     * Acepta funcion con argumento resolve y devuelve una funcion resolve
     *
     * @param callable $resolve <fn($root, array $args, AppContextInterface $context, ResolveInfo $info): mixed>
     * @return callable <fn($root, array $args, AppContextInterface $context, ResolveInfo $info): mixed>
     */
    public function handle(callable $resolve): callable;
}
