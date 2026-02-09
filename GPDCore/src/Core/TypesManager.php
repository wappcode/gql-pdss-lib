<?php

namespace GPDCore\Core;

class TypesManager
{
    protected array $types = [];

    /**
     * Adds a new type to the manager.
     *
     * @param string $name
     * @param mixed $type <ScalarType | class-string<ScalarType>>
     * @return void
     */
    public function add(string $name, mixed $type): void
    {
        $this->types[$name] = $type;
    }
    /**
     * Returns the type associated with the given name.
     *
     * @param string $name
     * @return mixed <ScalarType | class-string<ScalarType>>
     */
    public function get(string $name): mixed
    {
        return $this->types[$name] ?? null;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->types);
    }
}
