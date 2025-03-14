<?php

declare(strict_types=1);

namespace GPDCore\Library;

use GPDCore\Library\GPDApp;
use GraphQL\Doctrine\Types;


abstract class AbstractModule
{


    /**
     * @var IContextService
     */
    protected $context;

    /**
     *
     * @var bool
     */
    protected $productionMode;

    /**
     * @var GPDApp
     */
    protected $app;

    public function __construct(GPDApp $app)
    {
        $this->app = $app;
        $this->context = $this->app->getContext();
        $this->productionMode = $this->app->getProductionMode();
    }
    /**
     * Array con la configuración del módulo
     *
     * @return array
     */
    abstract function getConfig(): array;

    /**
     * Array con la configuración del módulo
     *
     * @return string
     */
    abstract function getSchema(): string;

    /**
     * Array con los servicios y tipos graphql que se necesitan para el módulo
     * El indice se utiliza como nombre del tipo
     *
     * @return array [invokables => [key: service], factories => [key: service], aliases => [key: service]]
     */
    abstract function getServicesAndGQLTypes(): array;

    /**
     * Array con los resolvers del módulo
     *
     * @return array array(string $key => callable $resolver)
     */
    abstract function getResolvers(): array;
    /**
     * Array con los graphql Queries del módulo
     *
     * @return array
     */
    abstract function getQueryFields(): array;
    /**
     * Array con los graphql mutations del módulo
     *
     * @return array
     */
    abstract function getMutationFields(): array;
}
