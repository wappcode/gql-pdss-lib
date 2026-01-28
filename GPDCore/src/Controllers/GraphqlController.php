<?php

namespace GPDCore\Controllers;

use GPDCore\Library\AbstractAppController;
use GPDCore\Services\GQLServer;
use Psr\Http\Message\ResponseInterface;

class GraphqlController extends AbstractAppController
{
    public function dispatch(): ResponseInterface
    {
        $content = $this->getJsonPayload() ?? [];
        $server = new GQLServer($this->app);
        return $server->start($content);
    }
}
