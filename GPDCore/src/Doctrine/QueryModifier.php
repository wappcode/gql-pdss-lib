<?php

declare(strict_types=1);

namespace GPDCore\Doctrine;

use Doctrine\ORM\QueryBuilder;
use GPDCore\Contracts\AppContextInterface;
use GPDCore\Contracts\QueryModifierInterface;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Modificador callable para QueryBuilders en resolvers de GraphQL.
 * 
 * Esta clase encapsula una función que modifica un QueryBuilder y puede ser
 * utilizada como un callable directamente gracias al método __invoke().
 */
class QueryModifier implements QueryModifierInterface
{
    /**
     * Función decoradora que modifica el QueryBuilder.
     * 
     * Firma esperada: function(QueryBuilder $qb, mixed $root, array $args, AppContextInterface $context, ResolveInfo $info): QueryBuilder
     */
    private ?callable $decorator = null;

    /**
     * Constructor.
     *
     * @param callable|null $decorator Función para modificar el QueryBuilder
     */
    public function __construct(?callable $decorator = null)
    {
        $this->decorator = $decorator;
    }

    /**
     * Método factory para crear una instancia de forma fluida.
     *
     * @param callable $decorator Función para modificar el QueryBuilder
     * @return self Nueva instancia de QueryModifier
     */
    public static function create(callable $decorator): self
    {
        return new self($decorator);
    }

    /**
     * Hace la clase invocable como un callable.
     * 
     * Ejecuta la función decoradora si está definida.
     *
     * @param QueryBuilder $qb QueryBuilder a modificar
     * @param mixed $root Valor raíz del resolver
     * @param array $args Argumentos de GraphQL
     * @param AppContextInterface $context Contexto de la aplicación
     * @param ResolveInfo $info Información de resolución de GraphQL
     * @return QueryBuilder QueryBuilder modificado o el original si no hay decorador
     */
    public function __invoke(QueryBuilder $qb, mixed $root, array $args, AppContextInterface $context, ResolveInfo $info): QueryBuilder
    {
        if ($this->decorator === null) {
            return $qb;
        }

        return ($this->decorator)($qb, $root, $args, $context, $info);
    }

    /**
     * Establece la función decoradora.
     *
     * @param callable|null $decorator Función para modificar el QueryBuilder
     * @return self Retorna la instancia para encadenamiento fluido
     */
    public function setDecorator(?callable $decorator): self
    {
        $this->decorator = $decorator;

        return $this;
    }

    /**
     * Obtiene la función decoradora.
     *
     * @return callable|null Función decoradora o null si no está definida
     */
    public function getDecorator(): ?callable
    {
        return $this->decorator;
    }
}
