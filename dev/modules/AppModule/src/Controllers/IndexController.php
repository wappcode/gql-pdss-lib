<?php

namespace AppModule\Controllers;

use GPDCore\Routing\AbstractAppController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class IndexController extends AbstractAppController
{
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {

        $id = $this->getRouteParam("id");
        $queryParams = $request->getQueryParams()["queryParams"] ?? "";


        throw new \Exception('Not implemented----' . $id . "__" . $queryParams);
    }
}
