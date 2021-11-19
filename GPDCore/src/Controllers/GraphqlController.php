<?php

namespace GPDCore\Controllers;

use GPDCore\Services\GQLServer;
use GPDCore\Library\AbstractAppController;

class GraphqlController extends AbstractAppController {

    public function dispatch() {
        $content = $this->request->getContent() ?? [];
        $server = new GQLServer();
        $server->start($content);
    }
}