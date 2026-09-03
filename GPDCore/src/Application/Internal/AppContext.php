<?php

declare(strict_types=1);

namespace GPDCore\Application\Internal;

use Doctrine\ORM\EntityManager;
use GPDCore\Contracts\AppConfigInterface;
use GPDCore\Contracts\AppContextInterface;
use Laminas\ServiceManager\ServiceManager;

final class AppContext implements AppContextInterface
{
    private function __construct(
        private readonly AppConfigInterface $config,
        private readonly ?EntityManager $entityManager = null,
        private readonly ?ServiceManager $serviceManager = null,
        private readonly string $enviroment = AppContextInterface::ENV_DEVELOPMENT,
        private array $contextAttributes = []
    ) {}

    public static function create(
        AppConfigInterface $config,
        ?EntityManager $entityManager = null,
        ?ServiceManager $serviceManager = null,
        string $enviroment = AppContextInterface::ENV_DEVELOPMENT
    ): self {
        return new self($config, $entityManager, $serviceManager, $enviroment);
    }

    public function getConfig(): AppConfigInterface
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
