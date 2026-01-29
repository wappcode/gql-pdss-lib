<?php

declare(strict_types=1);

namespace GPDCore\Services;

use Exception;
use GPDCore\Library\AppContextInterface;
use GPDCore\Library\DefaultArrayResolver;
use GPDCore\Library\GPDApp;
use GPDCore\Library\GQLFormattedError;
use GraphQL\Error\DebugFlag;
use GraphQL\Error\FormattedError;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use GraphQL\Validator\DocumentValidator;
use GraphQL\Validator\Rules\DisableIntrospection;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\StreamFactory;
use Psr\Http\Message\ResponseInterface;

class GQLServer
{

    protected GPDApp $app;
    protected AppContextInterface $context;
    protected ResponseFactory $responseFactory;
    protected StreamFactory $streamFactory;

    public function __construct(GPDApp $app)
    {
        $this->app = $app;
        $this->context = $app->getContext();
        $this->responseFactory = new ResponseFactory();
        $this->streamFactory = new StreamFactory();
    }



    /**
     * Inicializa el servidor graphql.
     */
    public function start(array $content): ResponseInterface
    {
        $productionMode = $this->app->isProductionMode();
        $schema = $this->getSchema();
        $queryString = $this->getQuery($content);
        $operationName = $this->getOperationName($content);
        $variableValues = $this->getVariables($content);
        $debug = $productionMode ? DebugFlag::NONE : DebugFlag::RETHROW_UNSAFE_EXCEPTIONS;
        $resolverManager = $this->app->getResolverManager();
        if ($productionMode) {
            DocumentValidator::addRule(new DisableIntrospection(1));
        }

        // @TODO agregar Query Complexity Analysis y Limiting Query Depth
        try {
            $fieldResolver = DefaultArrayResolver::createResolver($resolverManager);
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
                ->setErrorFormatter(GQLFormattedError::createFromException());
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


    protected function getSchema(): Schema
    {
        $schemaManager = $this->app->getSchemaManager();
        $typesManager = $this->app->getTypesManager();
        $schema = $schemaManager->buildSchema($typesManager);
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
