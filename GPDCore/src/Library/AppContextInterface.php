<?php

namespace GPDCore\Library;

use Doctrine\ORM\EntityManager;
use Laminas\Diactoros\ServerRequest;
use Laminas\ServiceManager\ServiceManager;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Contexto de aplicación inmutable que provee acceso a servicios y configuración.
 * 
 * Esta interfaz sigue el patrón de inmutabilidad similar a PSR-7 ServerRequestInterface.
 * Los métodos `with*` retornan una nueva instancia sin modificar la original.
 */
interface AppContextInterface
{
    public const ENV_PRODUCTION = 'production';
    public const ENV_DEVELOPMENT = 'development';
    public const ENV_TESTING = 'testing';

    /**
     * Retorna el servicio de configuración.
     */
    public function getConfig(): AppConfigInterface;

    /**
     * Retorna el gestor de entidades de Doctrine.
     * 
     * @return EntityManager|null Null si se ejecuta sin Doctrine
     */
    public function getEntityManager(): ?EntityManager;

    /**
     * Retorna el contenedor de servicios.
     * 
     * @return ServiceManager|null
     */
    public function getServiceManager(): ?ServiceManager;

    /**
     * Indica si la aplicación está en modo producción.
     */
    public function isProductionMode(): bool;

    /**
     * Retorna el entorno actual de la aplicación.
     * 
     * @return string Valores posibles: 'production', 'development', 'testing'
     */
    public function getEnviroment(): string;

    /**
     * Retorna un atributo asignado en el contexto.
     * 
     * @param string $name El nombre del atributo
     * @param mixed $default Valor por defecto si el atributo no existe
     * @return mixed
     */
    public function getContextAttribute(string $name, mixed $default = null): mixed;

    /**
     * Retorna una nueva instancia con el atributo especificado.
     * 
     * Este método DEBE ser implementado de forma que mantenga la inmutabilidad
     * del contexto original y retorne una nueva instancia con el atributo agregado.
     * La mejor práctica es agregar los atributos antes de asignar el contexto a la aplicación.
     * Pensado para utilizarse dentro de los resolvers Graphql que se generan en las funciones proxies
     * y otros casos donde se necesite pasar información adicional en el contexto pero que se asignen antes de inicializar la aplicación.
     * 
     * @param string $name El nombre del atributo
     * @param mixed $value El valor del atributo
     * @return static Nueva instancia con el atributo agregado
     */
    public function withContextAttribute(string $name, mixed $value): AppContextInterface;
}
