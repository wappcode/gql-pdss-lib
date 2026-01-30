<?php

declare(strict_types=1);

namespace GPDCore\Graphql;

use GPDCore\Contracts\MiddlewareInterface;
use InvalidArgumentException;

/**
 * Middleware para resolvers GraphQL.
 * 
 * Permite envolver resolvers con funcionalidad adicional mediante el patrón middleware,
 * útil para agregar lógica transversal como logging, autenticación, validación, caché, etc.
 */
class ResolverMiddleware
{
    /**
     * Envuelve un resolver con un middleware.
     * 
     * Un middleware es un callable o MiddlewareInterface que recibe el resolver original
     * y retorna uno nuevo que puede ejecutar lógica antes/después del resolver original.
     *
     * @param callable $resolver Resolver original
     * @param MiddlewareInterface|callable|null $middleware Middleware que envuelve al resolver
     * @return callable Resolver con middleware aplicado, o el original si middleware es null
     * 
     * @example
     * $middleware = function(callable $next) {
     *     return function($root, $args, $context, $info) use ($next) {
     *         // Lógica antes
     *         $result = $next($root, $args, $context, $info);
     *         // Lógica después
     *         return $result;
     *     };
     * };
     * $wrapped = ResolverMiddleware::wrap($originalResolver, $middleware);
     */
    public static function wrap(callable $resolver, MiddlewareInterface|callable|null $middleware): callable
    {
        if ($middleware === null) {
            return $resolver;
        }

        return $middleware($resolver);
    }

    /**
     * Encadena múltiples middlewares a un resolver en secuencia.
     * 
     * Los middlewares se aplican en orden inverso (de último a primero) para que
     * el primer middleware en el array sea el más externo en la cadena de ejecución.
     *
     * @param callable $resolver Resolver original
     * @param array<MiddlewareInterface|callable> $middlewares Array de middlewares
     * @return callable Resolver con todos los middlewares encadenados
     * @throws InvalidArgumentException Si algún elemento del array no es callable ni MiddlewareInterface
     * 
     * @example
     * $resolver = ResolverMiddleware::chain($original, [
     *     $authMiddleware,      // Se ejecuta primero
     *     $loggingMiddleware,   // Se ejecuta segundo
     *     $cachingMiddleware    // Se ejecuta tercero
     * ]);
     */
    public static function chain(callable $resolver, array $middlewares): callable
    {
        // Validar que todos los elementos sean callables o MiddlewareInterface
        foreach ($middlewares as $index => $middleware) {
            if (!is_callable($middleware) && !($middleware instanceof MiddlewareInterface)) {
                throw new InvalidArgumentException(
                    "Middleware at index {$index} must be callable or implement MiddlewareInterface"
                );
            }
        }

        // Aplicar middlewares en orden inverso
        $wrappedResolver = $resolver;
        $reversedMiddlewares = array_reverse($middlewares);

        foreach ($reversedMiddlewares as $middleware) {
            $wrappedResolver = self::wrap($wrappedResolver, $middleware);
        }

        return $wrappedResolver;
    }
}
