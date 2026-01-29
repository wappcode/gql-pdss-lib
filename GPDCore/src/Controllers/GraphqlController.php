<?php

namespace GPDCore\Controllers;

use GPDCore\Routing\AbstractAppController;
use GPDCore\Core\GPDApp;
use GPDCore\Services\GraphQLServer;
use Laminas\Diactoros\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GraphqlController extends AbstractAppController
{
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        $app = $request->getAttribute(GPDApp::class);
        $content = $this->getJsonPayload($request) ?? [];
        $server = new GraphQLServer($app);
        return $server->start($content);
    }
}
