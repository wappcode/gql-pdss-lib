<?php

namespace GPDCore\Contracts;

use GPDCore\Core\Application;

interface ModuleProviderInterface extends ServiceProviderInterface, MiddlewareProviderInterface, ResolverProviderInterface, TypesProviderInterface, SchemaProviderInterface, ConfigProviderInterface
{
    public function setApplication(
        Application $application,
    ): void;

    public function getApplication(): Application;
}
