<?php

declare(strict_types=1);

namespace GPDCore\Exceptions;

use Throwable;

/**
 * Excepción lanzada cuando no se encuentra una entidad solicitada.
 * Puede ser utilizada tanto en contextos GraphQL como en procesos que no sean GraphQL.
 */
class EntityNotFoundException extends GQLException
{
    public function __construct(string $message = 'Registro no encontrado', string $errorId = 'ENTITY_NOT_FOUND', int $httpcode = 404, ?Throwable $previous = null)
    {
        parent::__construct($message, $errorId, $httpcode, 'businessLogic', $previous);
    }
}
