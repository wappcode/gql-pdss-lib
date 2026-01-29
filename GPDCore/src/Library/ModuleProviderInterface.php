
<?php

namespace GPDCore\Library;

use Laminas\ServiceManager\ServiceManager;
use PSpell\Config;

interface ModuleProviderInterface extends ServiceProviderInterface,
    MiddlewareProviderInterface,
    ResolverProviderInterface,
    TypesProviderInterface,
    SchemaProviderInterface,
    ConfigProviderInterface
{
    public function registerModule(
        SchemaManager $schemaManager,
        ResolverManagerInterface $resolverManager,
        MiddlewareQueue $middlewareQueue,
        TypesManager $typesManager,
        AppConfigInterface $config,
        AppContextInterface $context,
        ?ServiceManager $serviceManager,
    ): void;
}
