<?php

declare(strict_types=1);

namespace GPDCore\Contracts;

/**
 * Interfaz para middlewares de resolvers GraphQL.
 *
 * Define el contrato que deben cumplir todos los middlewares que envuelven resolvers
 * con funcionalidad adicional (autenticación, autorización, logging, caché, etc.).
 *
 * Los middlewares deben ser invocables y recibir un resolver para retornar
 * otro resolver que incluya la lógica adicional.
 */
interface ResolverMiddlewareInterface
{
    /**
     * Aplica el middleware al resolver proporcionado.
     *
     * El middleware envuelve el resolver original con lógica adicional,
     * retornando un nuevo resolver que ejecuta código antes/después del original.
     *
     * @param callable $resolver Resolver original a envolver.
     *                           Firma: function(mixed $root, array $args, AppContextInterface $context, ResolveInfo $info): mixed
     *
     * @return callable Resolver envuelto con la lógica del middleware aplicada.
     *                  Debe mantener la misma firma que el resolver original.
     *
     * @example
     * // Implementación típica de un middleware
     * public function __invoke(callable $resolver): callable
     * {
     *     return function($root, $args, $context, $info) use ($resolver) {
     *         // Lógica antes (validación, autenticación, etc.)
     *         $result = $resolver($root, $args, $context, $info);
     *         // Lógica después (transformación, logging, etc.)
     *         return $result;
     *     };
     * }
     */
    public function __invoke(callable $resolver): callable;
}
