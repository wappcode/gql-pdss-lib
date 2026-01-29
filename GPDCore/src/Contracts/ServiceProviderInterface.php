<?php

namespace GPDCore\Contracts;

use Laminas\ServiceManager\ServiceManager;

interface ServiceProviderInterface
{
    public function registerServices(ServiceManager $serviceManager, AppContextInterface $context): void;
}
