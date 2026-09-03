<?php

namespace GraphqlModule;

use GPDCore\Controllers\GraphqlController;
use GPDCore\Application\Core\AbstractModule;
use GPDCore\Routing\RouteModel;
use GPDCore\Shared\Graphql\Types\DateTimeType;
use GPDCore\Shared\Graphql\Types\DateType;
use GPDCore\Shared\Graphql\Types\JSONData;

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
     * @return array<string, \GraphQL\Type\Definition\ScalarType | class-string<\GraphQL\Type\Definition\ScalarType>>
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
     * @return array<RouteModel>
     */
    public function getRoutes(): array
    {
        $GraphqlMethod = $this->application->isProductionMode() ? 'POST' : ['POST', 'GET'];
        return [new RouteModel($GraphqlMethod, $this->route, GraphqlController::class)];
    }
}
