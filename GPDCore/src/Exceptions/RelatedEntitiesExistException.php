<?php

declare(strict_types=1);

namespace GPDCore\Exceptions;

use Throwable;

/**
 * Excepción lanzada cuando se intenta eliminar una entidad que tiene elementos relacionados.
 * Puede ser utilizada tanto en contextos GraphQL como en procesos que no sean GraphQL.
 */
class RelatedEntitiesExistException extends GQLException
{
    public function __construct(string $message = 'Related elements must be deleted first', string $errorId = 'RELATED_ENTITIES_EXIST', int $httpcode = 409, ?Throwable $previous = null)
    {
        parent::__construct($message, $errorId, $httpcode, 'database', $previous);
    }
}
