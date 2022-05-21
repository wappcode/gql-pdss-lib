<?php

namespace GPDCore\Library;

use GPDCore\Services\ConfigService;
use Doctrine\ORM\EntityManager;
use GraphQL\Doctrine\Types;
use Laminas\ServiceManager\ServiceManager;

interface IContextService {
    public function init(string $enviroment, bool $productionMode): void;
    public function getEntityManager(): EntityManager;
    public function getConfig(): ConfigService;
    public function getServiceManager(): ServiceManager;
    public function getTypes(): Types;
    public function isProductionMode(): bool;
}