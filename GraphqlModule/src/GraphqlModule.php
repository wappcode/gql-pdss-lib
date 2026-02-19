<?php

namespace GraphqlModule;

use GPDCore\Controllers\GraphqlController;
use GPDCore\Core\AbstractModule;
use GPDCore\Graphql\Types\DateTimeType;
use GPDCore\Graphql\Types\DateType;
use GPDCore\Graphql\Types\JSONData;
use GPDCore\Routing\RouteModel;

class GraphqlModule extends AbstractModule
{


    public function __construct(private string $route = '/api') {}
    /**
     * Array con la configuración del módulo.
     */
    public function getConfig(): array
    {

        return [];
    }

    /**
     * Array con la configuración del módulo.
     */
    public function getSchema(): string
    {
        return file_get_contents(__DIR__ . '/../config/gql-pdss.graphqls') ?: '';
    }

    /**
     * Array con los servicios y tipos graphql que se necesitan para el módulo
     * El indice se utiliza como nombre del tipo.
     *
     * @return array [invokables => [key: service], factories => [key: service], aliases => [key: service]]
     */
    public function getServices(): array
    {

        return [];
    }

    /**
     * Array con los resolvers del módulo.
     *
     * @return array array(string $key => callable $resolver)
     */
    public function getResolvers(): array
    {

        return [];
    }

    public function getMiddlewares(): array
    {

        return [];
    }

    /**
     * Array con los tipos scalar graphql del módulo. El indice se utiliza como nombre del tipo.
     *
     * @return array<string, ScalarType | class-string<ScalarType>>
     */
    public function getTypes(): array
    {


        return [
            DateType::NAME => DateType::class,
            DateTimeType::NAME => DateTimeType::class,
            JSONData::NAME => JSONData::class,
        ];
    }

    /**
     * Array con las rutas REST del módulo. El indice se utiliza como path de la ruta.
     *
     * @return array<GPDCore\Routing\RouteModel>
     */
    public function getRoutes(): array
    {
        $GraphqlMethod = $this->application->isProductionMode() ? 'POST' : ['POST', 'GET'];
        return [new RouteModel($GraphqlMethod, $this->route, GraphqlController::class)];
    }
}
