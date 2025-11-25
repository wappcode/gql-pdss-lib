<?php

declare(strict_types=1);

namespace GPDCore\Library;

abstract class AbstractModule
{
    /**
     * @var IContextService
     */
    protected $context;

    /**
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
     * Array con la configuración del módulo.
     */
    abstract public function getConfig(): array;

    /**
     * Array con la configuración del módulo.
     */
    abstract public function getSchema(): string;

    /**
     * Array con los servicios y tipos graphql que se necesitan para el módulo
     * El indice se utiliza como nombre del tipo.
     *
     * @return array [invokables => [key: service], factories => [key: service], aliases => [key: service]]
     */
    abstract public function getServicesAndGQLTypes(): array;

    /**
     * Array con los resolvers del módulo.
     *
     * @return array array(string $key => callable $resolver)
     */
    abstract public function getResolvers(): array;
}
