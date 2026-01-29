<?php

namespace GPDCore\Core;

class TypesManager
{
    protected array $types = [];

    public function add(string $name, mixed $type): void
    {
        $this->types[$name] = $type;
    }

    public function get(string $name): mixed
    {
        return $this->types[$name] ?? null;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->types);
    }
}
