<?php

namespace GPDCore\Library;

use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\StreamFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractAppController implements AppControllerInterface
{

    protected ResponseFactory $responseFactory;
    protected StreamFactory $streamFactory;

    protected ?array $routeParams = null;
    public function __construct()
    {
        $this->responseFactory = new ResponseFactory();
        $this->streamFactory = new StreamFactory();
    }

    abstract public function dispatch(ServerRequestInterface $request): ResponseInterface;

    protected function createJsonResponse(array $data, int $status = 200): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($status)
            ->withHeader('Content-Type', 'application/json; charset=UTF-8');

        $body = $this->streamFactory->createStream(json_encode($data));
        $response = $response->withBody($body);

        return $response;
    }
    /**
     * Recupera un array con el payload JSON de la request
     * @return void
     */
    protected function getJsonPayload(ServerRequestInterface $request): ?array
    {
        $body = (string) $request->getBody();

        if (empty($body)) {
            return null;
        }

        return json_decode($body, true);
    }
    public function setRouteParams(?array $params): void
    {
        $this->routeParams = $params;
    }
    public function getRouteParam(string $name): mixed
    {
        return $this->routeParams[$name] ?? null;
    }
    public function getRouteParams(): ?array
    {
        return $this->routeParams;
    }
}
