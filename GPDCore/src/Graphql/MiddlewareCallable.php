<?php

declare(strict_types=1);

namespace GPDCore\Graphql;

use GPDCore\Contracts\AppContextInterface;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Middleware callable para resolvers de GraphQL.
 * 
 * Esta clase encapsula una función middleware que envuelve un resolver con lógica adicional
 * y puede ser utilizada como un callable directamente gracias al método __invoke().
 * 
 * Diseñada para trabajar con ResolverMiddleware, permite crear middlewares reutilizables
 * que pueden ser aplicados a múltiples resolvers.
 * 
 * Útil para aplicar lógica transversal como autenticación, autorización, logging,
 * validación, transformación de datos, etc.
 */
class MiddlewareCallable
{
    /**
     * Función middleware que envuelve el resolver.
     * 
     * Firma esperada: function(callable $resolver): callable
     * 
     * La función retornada debe tener la firma:
     * function(mixed $root, array $args, AppContextInterface $context, ResolveInfo $info): mixed
     */
    private ?callable $middleware = null;

    /**
     * Constructor.
     *
     * @param callable|null $middleware Función que recibe un resolver y retorna un resolver envuelto
     */
    public function __construct(?callable $middleware = null)
    {
        $this->middleware = $middleware;
    }

    /**
     * Método factory para crear una instancia de forma fluida.
     *
     * @param callable $middleware Función middleware para envolver el resolver
     * @return self Nueva instancia de MiddlewareCallable
     * 
     * @example
     * $middleware = MiddlewareCallable::create(function(callable $next) {
     *     return function($root, $args, $context, $info) use ($next) {
     *         // Lógica antes (autenticación, validación, etc.)
     *         $result = $next($root, $args, $context, $info);
     *         // Lógica después (transformación, logging, etc.)
     *         return $result;
     *     };
     * });
     * 
     * // Usar con ResolverMiddleware
     * $wrapped = ResolverMiddleware::wrap($originalResolver, $middleware);
     */
    public static function create(callable $middleware): self
    {
        return new self($middleware);
    }

    /**
     * Hace la clase invocable como un callable.
     * 
     * Ejecuta la función middleware si está definida, aplicándola al resolver proporcionado.
     * Compatible con ResolverMiddleware::wrap() y ResolverMiddleware::chain().
     *
     * @param callable $resolver Resolver original a envolver
     * @return callable Resolver envuelto o el original si no hay middleware
     * 
     * @example
     * $authMiddleware = new MiddlewareCallable(function(callable $next) {
     *     return function($root, $args, $context, $info) use ($next) {
     *         if (!$context->isAuthenticated()) {
     *             throw new UnauthorizedException();
     *         }
     *         return $next($root, $args, $context, $info);
     *     };
     * });
     * 
     * // Uso directo
     * $wrappedResolver = $authMiddleware($originalResolver);
     * 
     * // Uso con ResolverMiddleware
     * $wrappedResolver = ResolverMiddleware::wrap($originalResolver, $authMiddleware);
     */
    public function __invoke(callable $resolver): callable
    {
        if ($this->middleware === null) {
            return $resolver;
        }

        return ($this->middleware)($resolver);
    }

    /**
     * Establece la función middleware.
     *
     * @param callable|null $middleware Función middleware para envolver el resolver
     * @return self Retorna la instancia para encadenamiento fluido
     */
    public function setMiddleware(?callable $middleware): self
    {
        $this->middleware = $middleware;

        return $this;
    }

    /**
     * Obtiene la función middleware.
     *
     * @return callable|null Función middleware o null si no está definida
     */
    public function getMiddleware(): ?callable
    {
        return $this->middleware;
    }

    /**
     * Encadena múltiples middlewares en uno solo.
     * 
     * Los middlewares se aplican en orden (el primero se ejecuta primero).
     * Equivalente a ResolverMiddleware::chain() pero retorna un MiddlewareCallable.
     *
     * @param array<callable|MiddlewareCallable> $middlewares Array de middlewares
     * @return self Nueva instancia con todos los middlewares encadenados
     * 
     * @example
     * $chained = MiddlewareCallable::chain([
     *     $authMiddleware,      // Se ejecuta primero
     *     $loggingMiddleware,   // Se ejecuta segundo
     *     $cachingMiddleware    // Se ejecuta tercero
     * ]);
     * 
     * // Uso directo
     * $wrappedResolver = $chained($originalResolver);
     * 
     * // Uso con ResolverMiddleware
     * $wrappedResolver = ResolverMiddleware::wrap($originalResolver, $chained);
     */
    public static function chain(array $middlewares): self
    {
        return new self(function (callable $resolver) use ($middlewares) {
            $wrapped = $resolver;

            foreach ($middlewares as $middleware) {
                if ($middleware instanceof self) {
                    $wrapped = $middleware($wrapped);
                } elseif (is_callable($middleware)) {
                    $wrapped = $middleware($wrapped);
                }
            }

            return $wrapped;
        });
    }
}
