<?php

namespace GPDCore\Library;

use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\StreamFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractAppController
{
    protected ServerRequestInterface $request;

    /**
     * @var IContextService
     */
    protected $context;

    /**
     * @var GPDApp
     */
    protected $app;

    protected ResponseFactory $responseFactory;
    protected StreamFactory $streamFactory;
    public function __construct(ServerRequestInterface $request, GPDApp $app)
    {
        $this->request = $request;
        $this->app = $app;
        $this->context = $app->getContext();
        $this->responseFactory = new ResponseFactory();
        $this->streamFactory = new StreamFactory();
    }

    abstract public function dispatch();

    protected function createJsonResponse(array $data, int $status = 200): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($status)
            ->withHeader('Content-Type', 'application/json; charset=UTF-8');

        $body = $this->streamFactory->createStream(json_encode($data));
        $response = $response->withBody($body);

        return $response;
    }

    protected function emit(ResponseInterface $response): void
    {
        $emitter = new SapiEmitter();
        $emitter->emit($response);
    }
    /**
     * Recupera un array con el payload JSON de la request
     * @return void
     */
    protected function getJsonPayload(): ?array
    {
        $body = (string) $this->request->getBody();

        if (empty($body)) {
            return null;
        }

        return json_decode($body, true);
    }
}
