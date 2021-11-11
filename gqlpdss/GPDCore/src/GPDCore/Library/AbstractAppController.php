<?php 

namespace GPDCore\Library;

use GPDCore\Library\Request;


abstract class AbstractAppController {
    
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

   


    abstract public function dispatch();
}