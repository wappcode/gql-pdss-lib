<?php

namespace GPDCore\Contracts;


interface ResolverPipelineInterface
{
    public function pipe(ResolverMiddlewareInterface $middleware): void;

    // /**
    //  * Acepta funcion con argumento resolve y devuelve una funcion resolve
    //  *
    //  * @param callable $resolve <fn($root, array $args, AppContextInterface $context, ResolveInfo $info): mixed>
    //  * @return callable <fn($root, array $args, AppContextInterface $context, ResolveInfo $info): mixed>
    //  */
    // public function build(callable $resolve): callable;

    /**
     * Devuelve el resolver final
     */
    public function build(): callable;
}
