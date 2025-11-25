<?php

namespace GPDCore\Library;

use GraphQL\Error\Error;
use GraphQL\Error\FormattedError;

class GQLFormattedError
{
    public static function createFromException()
    {
        return function (Error $error) {
            $formated = FormattedError::createFromException($error);
            $previous = $error->getPrevious();
            if ($previous instanceof IGQLException) {
                $formated['id'] = $previous->getErrorId();
                $formated['code'] = $previous->getHttpcode();
            }

            return $formated;
        };
    }
}
