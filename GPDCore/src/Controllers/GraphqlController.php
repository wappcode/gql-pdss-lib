<?php

namespace GPDCore\Controllers;

use GPDCore\Library\AbstractAppController;
use GPDCore\Services\GQLServer;

class GraphqlController extends AbstractAppController
{
    public function dispatch()
    {
        $content = $this->request->getContent() ?? [];
        $server = new GQLServer($this->app);
        $server->start($content);
    }
}
