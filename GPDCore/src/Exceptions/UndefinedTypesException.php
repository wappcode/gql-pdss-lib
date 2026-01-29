<?php

namespace GPDCore\Exceptions;

use Exception;

class UndefinedTypesException extends Exception
{
    public function __construct()
    {
        parent::__construct('Undefined Types. You must enable doctrine');
    }
}
