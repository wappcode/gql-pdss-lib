<?php

declare(strict_types=1);

namespace GPDCore\Exceptions;

use Throwable;

/**
 * Excepción lanzada cuando se intenta insertar o actualizar un registro con una clave duplicada.
 * Puede ser utilizada tanto en contextos GraphQL como en procesos que no sean GraphQL.
 */
class DuplicateKeyException extends GQLException
{
    public function __construct(string $message = 'Duplicated Key', string $errorId = 'DUPLICATE_KEY', int $httpcode = 409, ?Throwable $previous = null)
    {
        parent::__construct($message, $errorId, $httpcode, 'database', $previous);
    }
}
