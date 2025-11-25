<?php

namespace GPDCore\Library;

use Exception;

class UndefinedTypesException extends Exception
{
    public function __construct()
    {
        parent::__construct('Undefined Types. You must enable doctrine');
    }
}
