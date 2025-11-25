<?php

namespace GPDCore\Library;

class ProxyUtilities
{
    /**
     * recupera un resolve aplicando un proxy que puede ejecutar una acción antes o despues de las acciones del resolve original.
     */
    public static function apply(callable $resolver, ?callable $proxy): callable
    {
        if (!is_callable($proxy)) {
            return $resolver;
        }

        return $proxy($resolver);
    }

    /**
     * Recupera un resolve actualizado por todos los proxies pasados en el parametro.
     */
    public static function applyAll(callable $resolver, array $proxies): callable
    {
        $proxiesSorted = array_reverse($proxies);
        $resolveUpdates = $resolver;
        foreach ($proxiesSorted as $proxy) {
            $resolveUpdates = self::apply($resolveUpdates, $proxy);
        }

        return $resolveUpdates;
    }
}
