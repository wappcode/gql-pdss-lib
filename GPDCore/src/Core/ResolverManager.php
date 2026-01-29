<?php

declare(strict_types=1);

namespace GPDCore\Core;

use GPDCore\Contracts\ResolverManagerInterface;


/**
 * Registra todos los resolvers de Doctrine Entities para ser usados por el resolver predeterminado del servidor.
 * 
 * @implements ResolverManagerInterface
 */
class ResolverManager implements ResolverManagerInterface
{
    /**
     * @var array<string, callable>
     */
    private array $resolvers = [];

    /**
     * Constructor público para crear instancias del gestor de resolvers.
     */
    public function __construct() {}

    /**
     * {@inheritDoc}
     */
    public function add(string $key, callable $resolver): void
    {
        $this->resolvers[$key] = $resolver;
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key): ?callable
    {
        return $this->resolvers[$key] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $key): bool
    {
        return isset($this->resolvers[$key]);
    }

    /**
     * {@inheritDoc}
     */
    public function remove(string $key): bool
    {
        if (isset($this->resolvers[$key])) {
            unset($this->resolvers[$key]);
            return true;
        }
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getKeys(): array
    {
        return array_keys($this->resolvers);
    }
}
