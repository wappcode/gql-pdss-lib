<?php

namespace GPDCore\Library;

/**
 * Contenedor singleton de configuración de la aplicación.
 * 
 * Maneja la configuración global accesible desde cualquier punto de la aplicación.
 * Proporciona métodos para obtener, verificar y actualizar valores de configuración.
 * 
 * @final
 */
final class AppConfig implements AppConfigInterface
{
    /**
     * @var self|null Instancia singleton
     */
    private static ?self $instance = null;


    /**
     * @var array<string, mixed> Almacenamiento de configuración
     */
    private array $config = [];

    /**
     * @var array<string, mixed> Configuración maestra que sobrescribe la configuración de módulos
     */
    private array $masterConfig = [];

    /**
     * Obtiene la instancia única de AppConfig.
     * 
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        // Si la clave existe en masterConfig, mergeamos recursivamente con config
        if (isset($this->masterConfig[$key])) {
            $baseValue = $this->config[$key] ?? null;

            // Si ambos son arrays, mergeamos recursivamente
            if (is_array($this->masterConfig[$key]) && is_array($baseValue)) {
                return array_replace_recursive($baseValue, $this->masterConfig[$key]);
            }

            // Si no son arrays, la configuración maestra tiene prioridad absoluta
            return $this->masterConfig[$key];
        }

        // Si no está en masterConfig, usar config normal
        return $this->config[$key] ?? $default;
    }

    /**
     * {@inheritDoc}
     */
    public function add(array $newConfig): self
    {
        $this->config = array_replace_recursive($this->config, $newConfig);

        return $this;
    }

    /**
     * Establece configuración maestra que sobrescribe la configuración de módulos.
     * 
     * Esta configuración no puede ser modificada por add() y siempre tiene precedencia
     * al obtener valores con get(). Debe establecerse al inicio de la aplicación.
     * 
     * @param array<string, mixed> $masterConfig Configuración maestra
     * @return self
     */
    public function setMasterConfig(array $masterConfig): self
    {
        $this->masterConfig = array_replace_recursive($this->masterConfig, $masterConfig);

        return $this;
    }


    /**
     * Constructor privado para implementar patrón singleton.
     * 
     * Se previene la instanciación desde fuera usando getInstance().
     */
    private function __construct() {}

    /**
     * Se previene la clonación de la instancia singleton.
     */
    private function __clone(): void {}

    /**
     * Se previene la des-serialización de la instancia singleton.
     */
    public function __wakeup(): void {}
}
