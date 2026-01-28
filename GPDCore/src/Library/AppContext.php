<?php

namespace GPDCore\Library;

use Doctrine\ORM\EntityManager;
use GPDCore\Services\ConfigService;
use Laminas\ServiceManager\ServiceManager;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Contexto de aplicación inmutable.
 * 
 * Provee acceso a servicios centrales de la aplicación siguiendo el patrón
 * de inmutabilidad. Los métodos `with*` retornan nuevas instancias.
 * 
 * Las instancias solo pueden ser creadas a través del método factory `create()`.
 */
final class AppContext implements AppContextInterface
{
    /**
     * @param array<string, mixed> $contextAttributes
     */
    private function __construct(
        protected readonly ConfigService $config,
        protected readonly ?EntityManager $entityManager,
        protected readonly ?ServiceManager $serviceManager,
        protected readonly ServerRequestInterface $request,
        protected readonly string $enviroment = AppContextInterface::ENV_DEVELOPMENT,
        protected array $contextAttributes = []
    ) {}

    /**
     * Crea una nueva instancia de AppContext.
     * 
     * @param ConfigService $config
     * @param EntityManager|null $entityManager
     * @param ServiceManager|null $serviceManager
     * @param ServerRequestInterface $request
     * @param string $enviroment Entorno de la aplicación (ej: 'production', 'development')
     * @return self
     */
    public static function create(
        ConfigService $config,
        ?EntityManager $entityManager,
        ?ServiceManager $serviceManager,
        ServerRequestInterface $request,
        string $enviroment = AppContextInterface::ENV_DEVELOPMENT
    ): self {
        return new self($config, $entityManager, $serviceManager, $request, $enviroment);
    }

    public function getConfig(): ConfigService
    {
        return $this->config;
    }

    public function getEntityManager(): ?EntityManager
    {
        return $this->entityManager;
    }

    public function getServiceManager(): ?ServiceManager
    {
        return $this->serviceManager;
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function isProductionMode(): bool
    {
        return $this->enviroment === AppContextInterface::ENV_PRODUCTION;
    }

    public function getEnviroment(): string
    {
        return $this->enviroment;
    }

    public function getContextAttribute(string $name, mixed $default = null): mixed
    {
        return $this->contextAttributes[$name] ?? $default;
    }

    public function withContextAttribute(string $name, mixed $value): AppContextInterface
    {
        $new = clone $this;
        $new->contextAttributes = $this->contextAttributes;
        $new->contextAttributes[$name] = $value;
        return $new;
    }
}
