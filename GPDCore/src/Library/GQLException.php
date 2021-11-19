<?php

namespace GPDCore\Library;

use Exception;
use GraphQL\Error\ClientAware;
use GPDCore\Library\IGQLException;

class GQLException extends Exception implements ClientAware, IGQLException
{

    protected $errorId;
    protected $httpcode;
    protected $category; 
    public function __construct($message='', $errorId = '', $httpcode = 400, $category = 'businessLogic', $previous = null)
    {
        parent::__construct($message, $httpcode, $previous);
        $this->category = $category;
        $this->errorId = $errorId;
        $this->httpcode = $httpcode;
    }
    public function isClientSafe()
    {
        return true;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function getErrorId()
    {
        return $this->errorId;
    }
    public function getHttpcode()
    {
        return $this->httpcode;
    }
}