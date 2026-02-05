<?php

declare(strict_types=1);

namespace GPDCore\Contracts;

/**
 * Interface para el gestor de resolvers de Doctrine Entities.
 */
interface ResolverManagerInterface
{
    /**
     * Registra un resolver con una clave específica.
     *
     * @param string   $key      Clave identificadora del resolver
     * @param callable $resolver Función resolver a registrar
     */
    public function add(string $key, callable $resolver): void;

    /**
     * Obtiene un resolver registrado por su clave.
     *
     * @param string $key Clave del resolver a obtener
     *
     * @return callable|null El resolver si existe, null en caso contrario
     */
    public function get(string $key): callable | ResolverPipelineInterface | null;

    /**
     * Verifica si existe un resolver registrado con la clave especificada.
     *
     * @param string $key Clave del resolver a verificar
     *
     * @return bool True si existe, false en caso contrario
     */
    public function has(string $key): bool;

    /**
     * Elimina un resolver registrado.
     *
     * @param string $key Clave del resolver a eliminar
     *
     * @return bool True si se eliminó, false si no existía
     */
    public function remove(string $key): bool;

    /**
     * Obtiene todas las claves de resolvers registrados.
     *
     * @return array<string> Array con las claves de los resolvers
     */
    public function getKeys(): array;
}
