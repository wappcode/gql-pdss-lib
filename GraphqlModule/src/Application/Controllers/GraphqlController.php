<?php

declare(strict_types=1);

namespace GraphqlModule\Application\Controllers;

use GPDCore\Application\Core\Application;
use GPDCore\Routing\AbstractAppController;
use GPDCore\Application\Services\GraphQLServer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GraphqlController extends AbstractAppController
{
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        $app = $request->getAttribute(Application::class);
        $content = $this->getJsonPayload($request) ?? [];
        $server = new GraphQLServer($app);

        return $server->start($content);
    }
}
