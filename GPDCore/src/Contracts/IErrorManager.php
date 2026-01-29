<?php

namespace GPDCore\Contracts;

interface IErrorManager
{
    public static function throwException(int $number, int $httpcode = 400, $category = 'businessLogic');
}
