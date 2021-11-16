<?php 

namespace GPDCore\Library;

use GPDCore\Library\Request;


abstract class AbstractAppController {
    
    protected $request;
    protected $context;

    public function __construct(Request $request, IContextService $context)
    {
        $this->request = $request;
        $this->context = $context;
    }

   


    abstract public function dispatch();
}