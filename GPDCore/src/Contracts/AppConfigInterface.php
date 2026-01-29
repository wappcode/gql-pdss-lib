<?php

namespace GPDCore\Contracts;

/**
 * Contrato para la configuración de la aplicación.
 * 
 * Permite acceso a valores de configuración con valores por defecto.
 */
interface AppConfigInterface
{
    /**
     * Obtiene un valor de configuración.
     * 
     * @param string $key Clave de configuración
     * @param mixed $default Valor por defecto si la clave no existe
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Agrega o actualiza valores de configuración de forma recursiva.
     * 
     * Utiliza array_replace_recursive para preservar la estructura de arrays anidados.
     * Los valores existentes se sobrescriben con los nuevos valores del mismo nivel.
     * 
     * @param array<string, mixed> $newConfig Array con nuevas configuraciones
     * @return self
     */
    public function add(array $newConfig): self;

    /**
     * Establece configuración maestra que sobrescribe la configuración de módulos.
     * 
     * Esta configuración tiene prioridad absoluta sobre cualquier configuración agregada
     * mediante add(). Debe establecerse al inicio de la aplicación y es inmutable después.
     * La configuración maestra es obligatoria para el correcto funcionamiento.
     * 
     * @param array<string, mixed> $masterConfig Configuración maestra
     * @return self
     */
    public function setMasterConfig(array $masterConfig): self;
}
