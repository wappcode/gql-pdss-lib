<?php

declare(strict_types=1);

namespace GPDCore\Services;

use Exception;
use GPDCore\Contracts\AppContextInterface;
use GPDCore\Core\Application;
use GPDCore\Exceptions\GQLException;
use GPDCore\Exceptions\GQLFormattedError;
use GPDCore\Graphql\ArrayFieldResolverFactory;
use GQLBasicClient\GQLClientException;
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

    /**
     * Constructor del servidor GraphQL.
     *
     * @param Application          $app                  La aplicación GPD
     * @param ResponseFactory|null $responseFactory      Factory para respuestas PSR-7
     * @param StreamFactory|null   $streamFactory        Factory para streams PSR-7
     * @param bool|null            $introspectionEnabled Si null, se deshabilita solo en producción
     * @param int|null             $maxQueryDepth        Profundidad máxima de queries (null = usar default)
     * @param int|null             $maxQueryComplexity   Complejidad máxima de queries (null = usar default)
     */
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

        // Configuración de seguridad con valores por defecto
        // Si introspectionEnabled es null, se determina automáticamente según el modo
        $this->introspectionEnabled = $introspectionEnabled ?? !$app->isProductionMode();
        $this->maxQueryDepth = $maxQueryDepth ?? self::DEFAULT_MAX_QUERY_DEPTH;
        $this->maxQueryComplexity = $maxQueryComplexity ?? self::DEFAULT_MAX_QUERY_COMPLEXITY;
    }

    /**
     * Inicializa y ejecuta el servidor GraphQL.
     *
     * @param array $content Contenido de la petición GraphQL
     *
     * @return ResponseInterface Respuesta HTTP PSR-7
     *
     * @throws Exception En modo desarrollo si hay un error
     */
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

        // Configurar reglas de seguridad
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
            // Manejar excepciones específicas de GraphQL
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

    /**
     * Valida que el contenido de la petición sea válido.
     *
     * @param array $content Contenido a validar
     *
     * @throws InvalidArgumentException Si el contenido no es válido
     */
    private function validateContent(array $content): void
    {
        if (empty($content['query']) && !isset($content['template']['data'])) {
            throw new InvalidArgumentException('GraphQL query or template is required');
        }
    }

    /**
     * Obtiene el schema GraphQL (con caché).
     *
     * @return Schema El schema de GraphQL
     */
    protected function getSchema(): Schema
    {
        if ($this->cachedSchema === null) {
            $schemaManager = $this->app->getSchemaManager();
            $typesManager = $this->app->getTypesManager();
            $this->cachedSchema = $schemaManager->buildSchema($typesManager);
        }

        return $this->cachedSchema;
    }

    /**
     * Configura las reglas de seguridad para validación de queries.
     *
     * @param bool $productionMode Si está en modo producción
     */
    private function configureSecurityRules(bool $productionMode): void
    {
        // Deshabilitar introspection si está configurado
        if (!$this->introspectionEnabled) {
            DocumentValidator::addRule(new DisableIntrospection(1));
        }

        // Limitar profundidad de queries (previene queries anidadas infinitas)
        DocumentValidator::addRule(new QueryDepth($this->maxQueryDepth));

        // Limitar complejidad de queries (previene queries costosas)
        DocumentValidator::addRule(new QueryComplexity($this->maxQueryComplexity));
    }

    /**
     * Obtiene si la introspección está habilitada.
     *
     * @return bool True si está habilitada
     */
    public function isIntrospectionEnabled(): bool
    {
        return $this->introspectionEnabled;
    }

    /**
     * Establece si la introspección está habilitada.
     *
     * @param bool $enabled True para habilitar, false para deshabilitar
     *
     * @return self Para method chaining
     */
    public function setIntrospectionEnabled(bool $enabled): self
    {
        $this->introspectionEnabled = $enabled;

        return $this;
    }

    /**
     * Obtiene el límite máximo de profundidad de queries.
     *
     * @return int Profundidad máxima permitida
     */
    public function getMaxQueryDepth(): int
    {
        return $this->maxQueryDepth;
    }

    /**
     * Establece el límite máximo de profundidad de queries.
     *
     * @param int $maxDepth Profundidad máxima permitida
     *
     * @return self Para method chaining
     */
    public function setMaxQueryDepth(int $maxDepth): self
    {
        $this->maxQueryDepth = $maxDepth;

        return $this;
    }

    /**
     * Obtiene el límite máximo de complejidad de queries.
     *
     * @return int Complejidad máxima permitida
     */
    public function getMaxQueryComplexity(): int
    {
        return $this->maxQueryComplexity;
    }

    /**
     * Establece el límite máximo de complejidad de queries.
     *
     * @param int $maxComplexity Complejidad máxima permitida
     *
     * @return self Para method chaining
     */
    public function setMaxQueryComplexity(int $maxComplexity): self
    {
        $this->maxQueryComplexity = $maxComplexity;

        return $this;
    }

    /**
     * Recupera el valor query de la consulta GraphQL.
     *
     * @param array $content Contenido de la petición
     *
     * @return string La query GraphQL
     */
    protected function getQuery(array $content): string
    {
        if (isset($content['template']['data'])) {
            return $this->findValueFromTemplate($content['template']['data'], 'query') ?? '';
        }

        return $content['query'] ?? '';
    }

    /**
     * Recupera el nombre de la operación GraphQL.
     *
     * @param array $content Contenido de la petición
     *
     * @return string|null El nombre de la operación
     */
    protected function getOperationName(array $content): ?string
    {
        if (isset($content['template']['data'])) {
            return $this->findValueFromTemplate($content['template']['data'], 'operationName');
        }

        return $content['operationName'] ?? null;
    }

    /**
     * Recupera las variables de la consulta GraphQL.
     *
     * @param array $content Contenido de la petición
     *
     * @return array|null Las variables de la consulta
     */
    protected function getVariables(array $content): ?array
    {
        if (isset($content['template']['data'])) {
            return $this->findValueFromTemplate($content['template']['data'], 'variables');
        }

        return $content['variables'] ?? null;
    }

    /**
     * Busca un valor en los datos del template.
     *
     * @param array  $data  Datos del template
     * @param string $value Nombre del valor a buscar
     *
     * @return mixed|null El valor encontrado o null
     */
    protected function findValueFromTemplate(array $data, string $value): mixed
    {
        foreach ($data as $item) {
            if (isset($item['name']) && $item['name'] === $value) {
                return $item['value'] ?? null;
            }
        }

        return null;
    }

    /**
     * Crea una respuesta JSON PSR-7.
     *
     * @param array $data   Datos a enviar en la respuesta
     * @param int   $status Código de estado HTTP
     *
     * @return ResponseInterface Respuesta PSR-7
     */
    protected function createJsonResponse(array $data, int $status = self::HTTP_OK): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($status)
            ->withHeader('Content-Type', self::CONTENT_TYPE_JSON);

        $body = $this->streamFactory->createStream(json_encode($data));

        return $response->withBody($body);
    }
}
