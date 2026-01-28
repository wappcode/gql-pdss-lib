<?php

declare(strict_types=1);

namespace GPDCore\Services;

use Exception;
use GPDCore\Library\DefaultArrayResolver;
use GPDCore\Library\GPDApp;
use GPDCore\Library\ResolverManager;
use GraphQL\Error\DebugFlag;
use GraphQL\Error\FormattedError;
use GraphQL\GraphQL;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use GraphQL\Validator\DocumentValidator;
use GraphQL\Validator\Rules\DisableIntrospection;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\StreamFactory;
use Laminas\ServiceManager\ServiceManager;
use Psr\Http\Message\ResponseInterface;

class GQLServer
{
    protected $gqlQueriesFields = [];

    protected $gqlMutationsFields = [];

    /**
     * @var AppContextInterface
     */
    protected $context;


    /**
     * @var string[]
     */
    protected array $schemasModules = [];

    /**
     * @var GPDApp
     */
    protected GPDApp $app;

    protected ResponseFactory $responseFactory;
    protected StreamFactory $streamFactory;

    public function __construct(GPDApp $app)
    {
        // Agrega el contexto (acceso a servicios y configuración compartidos a traves de toda la app)
        $this->app = $app;
        $this->context = $app->getContext();
        $this->responseFactory = new ResponseFactory();
        $this->streamFactory = new StreamFactory();
        $this->addModules();
    }

    /**
     * Agregar los módulos.
     */
    protected function addModules()
    {
        $modules = $this->app->getModules();
        // @var AbstractModule
        foreach ($modules as $module) {
            $this->addModule($module);
        }
    }

    /**
     * Agrega un módulo a la app (config, resolvers, queries y mutations).
     */
    protected function addModule(AbstractModule $module, $omitResolvers = false, $omitQueryFields = false, $omitMutationFields = false): GQLServer
    {
        $this->schemasModules[] = $module->getSchema();
        if (!$omitResolvers) {
            $resolvers = $module->getResolvers();
            $this->addResolvers($resolvers);
        }

        return $this;
    }

    /**
     * Agrega graphql queries fields a la lista.
     */
    protected function addGQLQueriesFields(array $queries): GQLServer
    {
        $this->gqlQueriesFields = array_merge($this->gqlQueriesFields, $queries);

        return $this;
    }

    /**
     * Agrega mutations a la lista.
     */
    protected function addGQLMutationsFields(array $mutations): GQLServer
    {
        $this->gqlMutationsFields = array_merge($this->gqlMutationsFields, $mutations);

        return $this;
    }




    /**
     * Agrega los resolvers de los módulos.
     */
    protected function addResolvers(array $resolvers)
    {
        foreach ($resolvers as $key => $resolver) {
            ResolverManager::add($key, $resolver);
        }
    }

    /**
     * Inicializa el servidor graphql.
     */
    public function start(array $content): ResponseInterface
    {
        $productionMode = $this->app->getProductionMode();
        $schema = $this->getSchema($this->context->getServiceManager());
        $queryString = $this->getQuery($content);
        $operationName = $this->getOperationName($content);
        $variableValues = $this->getVariables($content);
        $debug = $productionMode ? DebugFlag::NONE : DebugFlag::RETHROW_UNSAFE_EXCEPTIONS;

        if ($productionMode) {
            DocumentValidator::addRule(new DisableIntrospection(1));
        }

        // @TODO agregar Query Complexity Analysis y Limiting Query Depth
        try {
            $fieldResolver = new DefaultArrayResolver();
            $result = GraphQL::executeQuery(
                $schema,
                $queryString,
                $rootValue = null,
                $this->context,
                $variableValues,
                $operationName,
                $fieldResolver,
                $validationRules = null
            )
                ->seterrorFormatter(GQLFormattedError::createFromException());
            $responseData = $result->toArray($debug); // cambiar para mostrar errores (debug)
            $status = 200;
        } catch (Exception $e) {
            if ($productionMode) {
                $responseData = [
                    'errors' => [FormattedError::createFromException($e)],
                ];
                $status = 500;
            } else {
                throw $e;
            }
        }

        $response = $this->createJsonResponse($responseData, $status);
        return $response;
    }

    /**
     * Genera un ObjectType con query schema.
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
            'fields' => $this->gqlQueriesFields,
        ]);
    }

    /**
     * Genera un ObjectType con query schema.
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

    protected function getSchema(?ServiceManager $serviceManager): Schema
    {
        $typedefinitions = function (array $typeConfig, TypeDefinitionNode $typeDefinitionNode) use ($serviceManager) {
            $name = $typeConfig['name'];
            if ($serviceManager != null && $serviceManager->has($name)) {
                /** @var ScalarType */
                $type = $serviceManager->get($name);
                if ($type instanceof ScalarType) {
                    $config = [
                        'serialize' => function ($value) use ($type) {
                            return $type->serialize($value);
                        },
                        'parseValue' => function ($value) use ($type) {
                            return $type->parseValue($value);
                        },
                        'parseLiteral' => function ($valueNode) use ($type) {
                            return $type->parseLiteral($valueNode);

                            return $date;
                        },
                    ];

                    return array_merge($typeConfig, $config);
                }
            }

            return $typeConfig;
        };
        $schemaUtilities = file_get_contents(__DIR__ . '/../Assets/gql-pdss.graphqls');
        $allSchemas = [$schemaUtilities, ...$this->schemasModules];
        $schemasContent = GraphqlSchemaUtilities::combineSchemas($allSchemas);
        $queryField = preg_match("/type\sQuery/", $schemasContent) ? 'query: Query' : '';
        $mutationField = preg_match("/type\sMutation/", $schemasContent) ? 'mutation: Mutation' : '';
        $schemaBase = "schema {
                {$queryField}
                {$mutationField}
             }
        ";
        $appSchema = $schemaBase . PHP_EOL . $schemasContent;

        $schema = BuildSchema::build($appSchema, $typedefinitions);

        return $schema;
    }

    /**
     * Recupera el valor query de la consulta gql.
     *
     * @param string $content
     *
     * @return string
     */
    protected function getQuery($content)
    {
        if (isset($content['template']['data'])) {
            return $this->findValueFromTemplate($content['template']['data'], 'query');
        } else {
            return $content['query'] ?? '';
        }
    }

    protected function getOperationName($content)
    {
        if (isset($content['template']['data'])) {
            return $this->findValueFromTemplate($content['template']['data'], 'operationName');
        } else {
            return $content['operationName'] ?? null;
        }
    }

    protected function getVariables($content)
    {
        if (isset($content['template']['data'])) {
            return $this->findValueFromTemplate($content['template']['data'], 'variables');
        } else {
            return $content['variables'] ?? null;
        }
    }

    protected function findValueFromTemplate($data, $value)
    {
        $result = null;
        foreach ($data as $k => $item) {
            if ($item['name'] === $value) {
                return $item['value'];
            }
        }

        return $result;
    }

    protected function createJsonResponse(array $data, int $status = 200): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($status)
            ->withHeader('Content-Type', 'application/json; charset=UTF-8');

        $body = $this->streamFactory->createStream(json_encode($data));
        $response = $response->withBody($body);

        return $response;
    }
}
