<?php

declare(strict_types=1);

namespace GPDCore\Application\Graphql;

use ArrayAccess;
use GPDCore\Contracts\AppContextInterface;
use GPDCore\Contracts\ResolverManagerInterface;
use GPDCore\Contracts\ResolverPipelineInterface;
use GraphQL\Type\Definition\ResolveInfo;

final class ArrayFieldResolverFactory
{
    public static function create(ResolverManagerInterface $resolverManager): callable
    {
        return function (mixed $root, array $args, AppContextInterface $context, ResolveInfo $info) use ($resolverManager): mixed {
            $fieldName = $info->fieldName;

            if (!is_string($fieldName) || $fieldName === '') {
                return null;
            }

            $resolverKey = sprintf('%s::%s', $info->parentType->name, $fieldName);
            $resolver = $resolverManager->get($resolverKey);

            if ($resolver instanceof ResolverPipelineInterface) {
                $resolve = $resolver->build();
                return $resolve($root, $args, $context, $info);
            }
            if (is_callable($resolver)) {
                return $resolver($root, $args, $context, $info);
            }

            if (is_array($root)) {
                return $root[$fieldName] ?? null;
            }

            if ($root instanceof ArrayAccess) {
                return $root[$fieldName] ?? null;
            }

            return null;
        };
    }

    public static function createResolver(ResolverManagerInterface $resolverManager): callable
    {
        return self::create($resolverManager);
    }

    public static function createArrayFieldResolver(ResolverManagerInterface $resolverManager): callable
    {
        return self::create($resolverManager);
    }
}
