<?php 

namespace GPDCore\Library;

use GPDCore\Library\GPDApp;
use GPDCore\Library\Request;


abstract class AbstractAppController {
    
    protected $request;
    /**
     * @var IContextService
     */
    protected $context;
    /**
     *
     * @var GPDApp
     */
    protected $app;

    public function __construct(Request $request, GPDApp $app)
    {
        $this->request = $request;
        $this->context = $app->getContext();
    }

   


    abstract public function dispatch();
}