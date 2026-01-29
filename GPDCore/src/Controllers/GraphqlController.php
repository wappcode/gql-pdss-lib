<?php

namespace GPDCore\Controllers;

use GPDCore\Library\AbstractAppController;
use GPDCore\Library\GPDApp;
use GPDCore\Services\GQLServer;
use Laminas\Diactoros\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GraphqlController extends AbstractAppController
{
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        $app = $request->getAttribute(GPDApp::class);
        $content = $this->getJsonPayload($request) ?? [];
        $server = new GQLServer($app);
        return $server->start($content);
    }
}
