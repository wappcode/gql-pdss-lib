<?php

declare(strict_types=1);

namespace GPDCore\Application\Services;

use GPDCore\Contracts\AppContextInterface;
use GPDCore\Exceptions\GQLException;
use GPDCore\Exceptions\GQLFormattedError;
use GPDCore\Application\Graphql\ArrayFieldResolverFactory;
use GPDCore\Application\Core\Application;
use GraphQL\Error\DebugFlag;
use GraphQL\Error\FormattedError;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use GraphQL\Validator\DocumentValidator;
use GraphQL\Validator\Rules\DisableIntrospection;
use GraphQL\Validator\Rules\QueryComplexity;
use GraphQL\Validator\Rules\QueryDepth;
use InvalidArgumentException;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\StreamFactory;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class GraphQLServer
{
    private const HTTP_OK = 200;
    private const HTTP_INTERNAL_SERVER_ERROR = 500;
    private const CONTENT_TYPE_JSON = 'application/json; charset=UTF-8';

    // Límites de seguridad por defecto para queries
    private const DEFAULT_MAX_QUERY_DEPTH = 15;
    private const DEFAULT_MAX_QUERY_COMPLEXITY = 1000;

    protected Application $app;

    protected AppContextInterface $context;

    protected ResponseFactory $responseFactory;

    protected StreamFactory $streamFactory;

    private ?Schema $cachedSchema = null;

    // Configuración de seguridad
    private bool $introspectionEnabled;

    private int $maxQueryDepth;

    private int $maxQueryComplexity;

    public function __construct(
        Application $app,
        ?ResponseFactory $responseFactory = null,
        ?StreamFactory $streamFactory = null,
        ?bool $introspectionEnabled = null,
        ?int $maxQueryDepth = null,
        ?int $maxQueryComplexity = null
    ) {
        $this->app = $app;
        $this->context = $app->getContext();
        $this->responseFactory = $responseFactory ?? new ResponseFactory();
        $this->streamFactory = $streamFactory ?? new StreamFactory();

        $this->introspectionEnabled = $introspectionEnabled ?? !$app->isProductionMode();
        $this->maxQueryDepth = $maxQueryDepth ?? self::DEFAULT_MAX_QUERY_DEPTH;
        $this->maxQueryComplexity = $maxQueryComplexity ?? self::DEFAULT_MAX_QUERY_COMPLEXITY;
    }

    public function start(array $content): ResponseInterface
    {
        $this->validateContent($content);

        $productionMode = $this->app->isProductionMode();
        $schema = $this->getSchema();
        $queryString = $this->getQuery($content);
        $operationName = $this->getOperationName($content);
        $variableValues = $this->getVariables($content);
        $debug = $productionMode ? DebugFlag::NONE : DebugFlag::RETHROW_UNSAFE_EXCEPTIONS;
        $resolverManager = $this->app->getResolverManager();

        $this->configureSecurityRules($productionMode);

        try {
            $fieldResolver = ArrayFieldResolverFactory::create($resolverManager);
            $result = GraphQL::executeQuery(
                $schema,
                $queryString,
                null,
                $this->context,
                $variableValues,
                $operationName,
                $fieldResolver,
                null
            )->setErrorFormatter(GQLFormattedError::createFromException());

            $responseData = $result->toArray($debug);
            $status = self::HTTP_OK;
        } catch (GQLException $e) {
            $responseData = [
                'errors' => [GQLFormattedError::createFromException($e)],
            ];
            $status = self::HTTP_OK;
        } catch (Throwable $e) {
            if ($productionMode) {
                $responseData = [
                    'errors' => [FormattedError::createFromException($e)],
                ];
                $status = self::HTTP_INTERNAL_SERVER_ERROR;
            } else {
                $responseData = [
                    'errors' => [$e->getMessage()],
                ];
                $status = self::HTTP_INTERNAL_SERVER_ERROR;
            }
        }

        return $this->createJsonResponse($responseData, $status);
    }

    private function validateContent(array $content): void
    {
        if (empty($content['query']) && !isset($content['template']['data'])) {
            throw new InvalidArgumentException('GraphQL query or template is required');
        }
    }

    protected function getSchema(): Schema
    {
        if ($this->cachedSchema === null) {
            $schemaManager = $this->app->getSchemaManager();
            $typesManager = $this->app->getTypesManager();
            $this->cachedSchema = $schemaManager->buildSchema($typesManager);
        }

        return $this->cachedSchema;
    }

    private function configureSecurityRules(bool $productionMode): void
    {
        if (!$this->introspectionEnabled) {
            DocumentValidator::addRule(new DisableIntrospection(1));
        }

        DocumentValidator::addRule(new QueryDepth($this->maxQueryDepth));
        DocumentValidator::addRule(new QueryComplexity($this->maxQueryComplexity));
    }

    public function isIntrospectionEnabled(): bool
    {
        return $this->introspectionEnabled;
    }

    public function setIntrospectionEnabled(bool $enabled): self
    {
        $this->introspectionEnabled = $enabled;

        return $this;
    }

    public function getMaxQueryDepth(): int
    {
        return $this->maxQueryDepth;
    }

    public function setMaxQueryDepth(int $maxDepth): self
    {
        $this->maxQueryDepth = $maxDepth;

        return $this;
    }

    public function getMaxQueryComplexity(): int
    {
        return $this->maxQueryComplexity;
    }

    public function setMaxQueryComplexity(int $maxComplexity): self
    {
        $this->maxQueryComplexity = $maxComplexity;

        return $this;
    }

    protected function getQuery(array $content): string
    {
        if (isset($content['template']['data'])) {
            return $this->findValueFromTemplate($content['template']['data'], 'query') ?? '';
        }

        return $content['query'] ?? '';
    }

    protected function getOperationName(array $content): ?string
    {
        if (isset($content['template']['data'])) {
            return $this->findValueFromTemplate($content['template']['data'], 'operationName');
        }

        return $content['operationName'] ?? null;
    }

    protected function getVariables(array $content): ?array
    {
        if (isset($content['template']['data'])) {
            return $this->findValueFromTemplate($content['template']['data'], 'variables');
        }

        return $content['variables'] ?? null;
    }

    protected function findValueFromTemplate(array $data, string $value): mixed
    {
        foreach ($data as $item) {
            if (isset($item['name']) && $item['name'] === $value) {
                return $item['value'] ?? null;
            }
        }

        return null;
    }

    protected function createJsonResponse(array $data, int $status = self::HTTP_OK): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($status)
            ->withHeader('Content-Type', self::CONTENT_TYPE_JSON);

        $body = $this->streamFactory->createStream(json_encode($data));

        return $response->withBody($body);
    }
}
