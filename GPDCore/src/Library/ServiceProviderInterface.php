<?php

namespace GPDCore\Library;

use Laminas\ServiceManager\ServiceManager;

interface ServiceProviderInterface
{
    public function registerServices(ServiceManager $serviceManager, AppContextInterface $context): void;
}
