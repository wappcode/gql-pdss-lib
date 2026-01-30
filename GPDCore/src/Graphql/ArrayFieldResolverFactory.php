<?php

declare(strict_types=1);

namespace GPDCore\Graphql;

use ArrayAccess;
use GPDCore\Contracts\AppContextInterface;
use GPDCore\Contracts\ResolverManagerInterface;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Factory para crear resolvers de campos que operan sobre arrays.
 *
 * Permite acceso a elementos de array por clave. Si existe un resolver
 * personalizado registrado en el ResolverManager, lo usará; de lo contrario,
 * intentará acceder al valor del array usando el nombre del campo como clave.
 */
final class ArrayFieldResolverFactory
{
    /**
     * Crea un resolver de campo para datos tipo array.
     *
     * @param ResolverManagerInterface $resolverManager Manager que contiene los resolvers personalizados
     *
     * @return callable Resolver que acepta ($root, $args, $context, $info) y retorna mixed
     */
    public static function create(ResolverManagerInterface $resolverManager): callable
    {
        return function (mixed $root, array $args, AppContextInterface $context, ResolveInfo $info) use ($resolverManager): mixed {
            $fieldName = $info->fieldName;

            if (!is_string($fieldName) || $fieldName === '') {
                return null;
            }

            // Intentar usar resolver personalizado primero
            $resolverKey = sprintf('%s::%s', $info->parentType->name, $fieldName);
            $resolver = $resolverManager->get($resolverKey);

            if (is_callable($resolver)) {
                return $resolver($root, $args, $context, $info);
            }

            // Fallback a acceso directo de array
            if (is_array($root)) {
                return $root[$fieldName] ?? null;
            }

            // Si $root implementa ArrayAccess
            if ($root instanceof ArrayAccess) {
                return $root[$fieldName] ?? null;
            }

            return null;
        };
    }

    /**
     * Alias para compatibilidad hacia atrás.
     *
     * @deprecated Use create() instead
     */
    public static function createResolver(ResolverManagerInterface $resolverManager): callable
    {
        return self::create($resolverManager);
    }

    /**
     * Alias para compatibilidad hacia atrás.
     *
     * @deprecated Use create() instead
     */
    public static function createArrayFieldResolver(ResolverManagerInterface $resolverManager): callable
    {
        return self::create($resolverManager);
    }
}
