<?php

declare(strict_types=1);

namespace GPDCore\Services;

use Exception;
use GPDCore\Contracts\AppContextInterface;
use GPDCore\Graphql\DefaultArrayResolver;
use GPDCore\Core\GPDApp;
use GPDCore\Exceptions\GQLFormattedError;
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

class GraphQLServer
{
    private const HTTP_OK = 200;
    private const HTTP_INTERNAL_SERVER_ERROR = 500;
    private const CONTENT_TYPE_JSON = 'application/json; charset=UTF-8';

    // Límites de seguridad para queries
    private const MAX_QUERY_DEPTH = 15;
    private const MAX_QUERY_COMPLEXITY = 1000;

    protected GPDApp $app;
    protected AppContextInterface $context;
    protected ResponseFactory $responseFactory;
    protected StreamFactory $streamFactory;
    private ?Schema $cachedSchema = null;

    /**
     * Constructor del servidor GraphQL.
     *
     * @param GPDApp $app La aplicación GPD
     * @param ResponseFactory|null $responseFactory Factory para respuestas PSR-7
     * @param StreamFactory|null $streamFactory Factory para streams PSR-7
     */
    public function __construct(
        GPDApp $app,
        ?ResponseFactory $responseFactory = null,
        ?StreamFactory $streamFactory = null
    ) {
        $this->app = $app;
        $this->context = $app->getContext();
        $this->responseFactory = $responseFactory ?? new ResponseFactory();
        $this->streamFactory = $streamFactory ?? new StreamFactory();
    }

    /**
     * Inicializa y ejecuta el servidor GraphQL.
     *
     * @param array $content Contenido de la petición GraphQL
     * @return ResponseInterface Respuesta HTTP PSR-7
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
            $fieldResolver = DefaultArrayResolver::createResolver($resolverManager);
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
        } catch (Exception $e) {
            if ($productionMode) {
                $responseData = [
                    'errors' => [FormattedError::createFromException($e)],
                ];
                $status = self::HTTP_INTERNAL_SERVER_ERROR;
            } else {
                throw $e;
            }
        }

        return $this->createJsonResponse($responseData, $status);
    }

    /**
     * Valida que el contenido de la petición sea válido.
     *
     * @param array $content Contenido a validar
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
        // Deshabilitar introspection en producción (evita que descubran el schema)
        if ($productionMode) {
            DocumentValidator::addRule(new DisableIntrospection(1));
        }

        // Limitar profundidad de queries (previene queries anidadas infinitas)
        $maxDepth = $this->getMaxQueryDepth();
        DocumentValidator::addRule(new QueryDepth($maxDepth));

        // Limitar complejidad de queries (previene queries costosas)
        $maxComplexity = $this->getMaxQueryComplexity();
        $queryComplexity = new QueryComplexity($maxComplexity);
        DocumentValidator::addRule($queryComplexity);
    }

    /**
     * Obtiene el límite máximo de profundidad de queries.
     * Puede ser sobrescrito para obtener desde configuración.
     *
     * @return int Profundidad máxima permitida
     */
    protected function getMaxQueryDepth(): int
    {
        // Opcionalmente puedes obtenerlo desde la configuración:
        // return $this->app->getConfig()->get('graphql.max_depth', self::MAX_QUERY_DEPTH);
        return self::MAX_QUERY_DEPTH;
    }

    /**
     * Obtiene el límite máximo de complejidad de queries.
     * Puede ser sobrescrito para obtener desde configuración.
     *
     * @return int Complejidad máxima permitida
     */
    protected function getMaxQueryComplexity(): int
    {
        // Opcionalmente puedes obtenerlo desde la configuración:
        // return $this->app->getConfig()->get('graphql.max_complexity', self::MAX_QUERY_COMPLEXITY);
        return self::MAX_QUERY_COMPLEXITY;
    }

    /**
     * Recupera el valor query de la consulta GraphQL.
     *
     * @param array $content Contenido de la petición
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
     * @param array $data Datos del template
     * @param string $value Nombre del valor a buscar
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
     * @param array $data Datos a enviar en la respuesta
     * @param int $status Código de estado HTTP
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
