<?php

declare(strict_types=1);

namespace GPDCore\Exceptions;

use Throwable;

/**
 * Excepción lanzada cuando se proporcionan parámetros de paginación inválidos.
 * Puede ser utilizada tanto en contextos GraphQL como en procesos que no sean GraphQL.
 */
class InvalidPaginationException extends GQLException
{
    public function __construct(string $message = 'Invalid pagination parameters', string $errorId = 'INVALID_PAGINATION', int $httpcode = 400, ?Throwable $previous = null)
    {
        parent::__construct($message, $errorId, $httpcode, 'validation', $previous);
    }
}
