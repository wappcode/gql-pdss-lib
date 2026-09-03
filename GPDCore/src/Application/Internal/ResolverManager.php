<?php

declare(strict_types=1);

namespace GPDCore\Application\Internal;

use GPDCore\Contracts\ResolverManagerInterface;
use GPDCore\Contracts\ResolverPipelineInterface;

class ResolverManager implements ResolverManagerInterface
{
    private array $resolvers = [];

    public function __construct() {}

    public function add(string $key, callable | ResolverPipelineInterface $resolver): void
    {
        $this->resolvers[$key] = $resolver;
    }

    public function get(string $key): callable | ResolverPipelineInterface | null
    {
        return $this->resolvers[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return isset($this->resolvers[$key]);
    }

    public function remove(string $key): bool
    {
        if (isset($this->resolvers[$key])) {
            unset($this->resolvers[$key]);

            return true;
        }

        return false;
    }

    public function getKeys(): array
    {
        return array_keys($this->resolvers);
    }
}
