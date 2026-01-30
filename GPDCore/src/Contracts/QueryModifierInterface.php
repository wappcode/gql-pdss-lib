<?php

declare(strict_types=1);

namespace GPDCore\Contracts;

use Doctrine\ORM\QueryBuilder;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Interfaz para modificadores de QueryBuilder.
 *
 * Define el contrato para clases que modifican QueryBuilders en el contexto
 * de resolvers de GraphQL. Los implementadores deben ser callables.
 */
interface QueryModifierInterface
{
    /**
     * Modifica un QueryBuilder aplicando lógica personalizada.
     *
     * @param QueryBuilder        $qb      QueryBuilder a modificar
     * @param mixed               $root    Valor raíz del resolver
     * @param array               $args    Argumentos de GraphQL
     * @param AppContextInterface $context Contexto de la aplicación
     * @param ResolveInfo         $info    Información de resolución de GraphQL
     *
     * @return QueryBuilder QueryBuilder modificado
     */
    public function __invoke(
        QueryBuilder $qb,
        mixed $root,
        array $args,
        AppContextInterface $context,
        ResolveInfo $info
    ): QueryBuilder;
}
