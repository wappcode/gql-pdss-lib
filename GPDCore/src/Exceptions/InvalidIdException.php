<?php

declare(strict_types=1);

namespace GPDCore\Exceptions;

use Throwable;

/**
 * Excepción lanzada cuando se proporciona un ID inválido o vacío.
 * Puede ser utilizada tanto en contextos GraphQL como en procesos que no sean GraphQL.
 */
class InvalidIdException extends GQLException
{
    public function __construct(string $message = 'Id Inválido', string $errorId = 'INVALID_ID', int $httpcode = 400, ?Throwable $previous = null)
    {
        parent::__construct($message, $errorId, $httpcode, 'validation', $previous);
    }
}
