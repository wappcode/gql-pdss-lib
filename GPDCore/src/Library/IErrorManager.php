<?php

namespace GPDCore\Library;

interface IErrorManager
{
    public static function throwException(int $number, int $httpcode = 400, $category = 'businessLogic');
}
