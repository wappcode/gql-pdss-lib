<?php

namespace GPDCore\Contracts;

/**
 * @template T of object
 */
interface EntityDataMapperInterface
{
    /**
     * Instancia y puebla una entidad nueva desde un array.
     *
     * @param class-string<T> $entityClass
     * @param array<string, mixed> $data
     * @return T
     */
    public function createEntity(string $entityClass, array $data): object;

    /**
     * Puebla una entidad existente (por ejemplo, para un Update/PUT).
     *
     * @param T $entity
     * @param array<string, mixed> $data
     * @return T
     */
    public function updateEntity(object $entity, array $data): object;
}
