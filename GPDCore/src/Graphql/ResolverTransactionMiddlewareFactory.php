<?php

namespace GPDCore\Graphql;

use GPDCore\Contracts\ResolverMiddlewareInterface;

class ResolverTransactionMiddlewareFactory
{
    public static function createMiddleware(): ResolverMiddlewareInterface
    {
        $proxy = function (callable $resolve) {
            return function ($root, array $args, $context, $info) use ($resolve) {
                $entityManager = $context->getEntityManager();
                $entityManager->beginTransaction();
                try {
                    $result = $resolve($root, $args, $context, $info);
                    $entityManager->commit();
                    return $result;
                } catch (\Throwable $e) {
                    $entityManager->rollBack();
                    throw $e; // Re-lanza la excepción para que sea manejada por GraphQL
                }
            };
        };
        return new ResolverWrapperMiddleware($proxy);
    }
}
