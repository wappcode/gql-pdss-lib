<?php

declare(strict_types=1);

namespace GPDCore\Library;

use Exception;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use GPDCore\Library\GPDApp;
use GraphQL\Doctrine\Types;
use GraphQL\Error\DebugFlag;
use GraphQL\Error\FormattedError;
use GPDCore\Library\AbstractModule;
use GPDCore\Graphql\ResolverManager;
use GPDCore\Library\GQLFormattedError;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Validator\DocumentValidator;
use GPDCore\Graphql\DefaultArrayResolver;
use GraphQL\Validator\Rules\DisableIntrospection;
use Laminas\ServiceManager\ServiceManager;

abstract class AbstractGQLServer
{

    protected $gqlQueriesFields = [];
    protected $gqlMutationsFields = [];
    protected $servicesAndGQLTypes = [];
    /**
     *
     * @var IContextService
     */
    protected $context;
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     *
     * @var GPDApp
     */
    protected $app;


    protected function __construct(GPDApp $app){
        // Agrega el contexto (acceso a servicios y configuración compartidos a traves de toda la app)
        $this->app = $app;
        $this->context = $app->getContext();
        $this->serviceManager = $this->context->getServiceManager();
        $this->addModules();
        $this->registerTypes();
    }
    /**
     * Agregar los módulos
     *
     * @return void
     */
    protected function addModules()
    {
        $modules = $this->app->getModules();
        /** @var AbstractModule */
        foreach ($modules as $module) {
            $this->addModule($module);
        }
    }



    /**
     * Agrega un módulo a la app (config, resolvers, queries y mutations)
     *
     * @return void
     */
    protected function addModule(AbstractModule $module, $omitResolvers = false, $omitQueryFields = false, $omitMutationFields = false): AbstractGQLServer
    {

        if (!$omitResolvers) {
            $resolvers = $module->getResolvers();
            $this->addResolvers($resolvers);
        }
        if (!$omitQueryFields) {
            $queries = $module->getQueryFields();
            $this->addGQLQueriesFields($queries);
        }
        if (!$omitMutationFields) {
            $mutations = $module->getMutationFields();
            $this->addGQLMutationsFields($mutations);
        }


        return $this;
    }

    /**
     * Agrega graphql queries fields a la lista
     *
     * @return void
     */
    protected function addGQLQueriesFields(array $queries): AbstractGQLServer
    {
        $this->gqlQueriesFields = array_merge($this->gqlQueriesFields, $queries);
        return $this;
    }
    /**
     * Agrega mutations a la lista
     *
     * @return void
     */
    protected function addGQLMutationsFields(array $mutations): AbstractGQLServer
    {
        $this->gqlMutationsFields = array_merge($this->gqlMutationsFields, $mutations);
        return $this;
    }

    protected function addServicesAndGQLTypes(array $services): AbstractGQLServer
    {
        $factories = $services["factories"] ?? [];
        $invokables = $services["invokables"] ?? [];
        $aliases = $services["aliases"] ?? [];
        $selfInvokables = $this->servicesAndGQLTypes["invokables"] ?? [];
        $selfFactories = $this->servicesAndGQLTypes["factories"] ?? [];
        $selfAliases = $this->servicesAndGQLTypes["aliases"] ?? [];
        $this->servicesAndGQLTypes["invokables"] = array_merge($selfInvokables, $invokables);
        $this->servicesAndGQLTypes["factories"] = array_merge($selfFactories, $factories);
        $this->servicesAndGQLTypes["aliases"] = array_merge($selfAliases, $aliases);
        return $this;
    }

    protected function registerTypes()
    {
        $invokables = $this->servicesAndGQLTypes["invokables"] ?? [];
        $factories = $this->servicesAndGQLTypes["factories"] ?? [];
        $aliases = $this->servicesAndGQLTypes["aliases"] ?? [];
        foreach($invokables as $k => $invokable) {
            $this->serviceManager->setInvokableClass($k, $invokable);
        }
        foreach ($factories as $k => $factory) {
            $this->serviceManager->setFactory($k, $factory);
        }
        
        foreach($aliases as $k => $alias) {
            $this->serviceManager->setAlias($k, $aliases);
        }
    }


    /**
     * Agrega los resolvers de los módulos
     *
     * @param array $resolvers
     * @return void
     */
    protected function addResolvers(array $resolvers)
    {
        foreach ($resolvers as $key => $resolver) {
            ResolverManager::add($key, $resolver);
        }
    }


    /**
     * Inicializa el servidor graphql
     *
     * @param array $content
     * @return void
     */
    public function start(array $content)
    {
        $productionMode = $this->app->getProductionMode();
        $types = $this->context->getTypes();
        $schema = $this->getSchema($types);
        $queryString = $this->getQuery($content);
        $operationName = $this->getOperationName($content);
        $variableValues = $this->getVariables($content);
        $debug =   $productionMode ?  DebugFlag::NONE :  DebugFlag::RETHROW_UNSAFE_EXCEPTIONS;

        if ($productionMode) {
            DocumentValidator::addRule(new DisableIntrospection());
        }
        // @TODO agregar Query Complexity Analysis y Limiting Query Depth
        try {
            $result = GraphQL::executeQuery(
                $schema,
                $queryString,
                $rootValue = null,
                $context = $this->context,
                $variableValues,
                $operationName,
                $fieldResolver = new DefaultArrayResolver(),
                $validationRules = null
            )
                ->seterrorFormatter(GQLFormattedError::createFromException());
            $response = $result->toArray($debug); // cambiar para mostrar errores (debug)
            $status = 200;
        } catch (Exception $e) {
            if ($productionMode) {
                $response = [
                    'errors' => [FormattedError::createFromException($e)]
                ];
                $status = 500;
            } else {
                throw $e;
            }
        }
        header("Content-Type: application/json; charset=UTF-8", true, $status);
        echo json_encode($response);
    }

    /**
     * Genera un ObjectType con query schema 
     *
     * @return ObjectType
     */
    protected function getGQLQueriesFields()
    {
        if (empty($this->gqlQueriesFields)) {
            return null;
        }
        return new ObjectType([
            'name' => 'Query',
            'fields' => $this->gqlQueriesFields
        ]);
    }
    /**
     * Genera un ObjectType con query schema 
     *
     * @return ObjectType
     */
    protected function getGQLMutationsFields()
    {
        if (empty($this->gqlMutationsFields)) {
            return null;
        }
        return new ObjectType([
            'name' => 'Mutation',
            'fields' => $this->gqlMutationsFields,
        ]);
    }

    protected function getSchema(Types $types)
    {
        $query = $this->getGQLQueriesFields();
        $mutations = $this->getGQLMutationsFields();
        return new Schema([
            'query' => $query,
            'mutation' =>   $mutations,
            'typeLoader' => function ($name) use ($types) {
                $type = $types->get($name);
                return $type;
            }
        ]);
    }
    /**
     * Recupera el valor query de la consulta gql
     *
     * @param string $content
     * @return void
     */
    protected function getQuery($content)
    {
        if (isset($content["template"]["data"])) {
            return $this->findValueFromTemplate($content["template"]["data"], "query");
        } else {
            return $content["query"] ?? null;
        }
    }

    protected function getOperationName($content)
    {
        if (isset($content["template"]["data"])) {
            return $this->findValueFromTemplate($content["template"]["data"], "operationName");
        } else {
            return $content["operationName"] ?? null;
        }
    }
    protected function getVariables($content)
    {
        if (isset($content["template"]["data"])) {
            return $this->findValueFromTemplate($content["template"]["data"], "variables");
        } else {
            return $content["variables"] ?? null;
        }
    }

    protected function findValueFromTemplate($data, $value)
    {
        $result = null;
        foreach ($data as $k => $item) {
            if ($item["name"] === $value) {
                return $item["value"];
            }
        }
        return $result;
    }
}
