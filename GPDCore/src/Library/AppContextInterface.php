<?php

namespace GPDCore\Library;

use Doctrine\ORM\EntityManager;
use GPDCore\Services\ConfigService;
use Laminas\ServiceManager\ServiceManager;

interface AppContextInterface
{
    public function getEntityManager(): ?EntityManager;

    public function getConfig(): ConfigService;

    public function getServiceManager(): ServiceManager;

    public function isProductionMode(): bool;

    public function getAttribute(string $name);

    /** Solo util en resolvers */
    public  function withAttribute(string $name, $value): AppContextInterface;
}
