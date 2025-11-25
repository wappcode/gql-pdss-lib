<?php

namespace GPDCore\Library;

abstract class AbstractAppController
{
    protected $request;

    /**
     * @var IContextService
     */
    protected $context;

    /**
     * @var GPDApp
     */
    protected $app;

    public function __construct(Request $request, GPDApp $app)
    {
        $this->request = $request;
        $this->app = $app;
        $this->context = $app->getContext();
    }

    abstract public function dispatch();
}
