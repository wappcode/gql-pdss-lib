<?php

declare(strict_types=1);

namespace GPDCore\Library;

use GraphQL\Type\Definition\ResolveInfo;

/**
 * A field resolver that will allow access to public properties and getter.
 * Arguments, if any, will be forwarded as is to the method.
 */
final class DefaultArrayResolver
{

    public static function createResolver(ResolverManagerInterface $resolverManager): callable
    {
        return function ($root, $args, AppContextInterface $context, ResolveInfo $info) use ($resolverManager) {
            /** @var string $fieldName */
            $fieldName = $info->fieldName;
            $resolverKey = sprintf('%s::%s', $info->parentType->name, $fieldName);
            $resolver = $resolverManager->get($resolverKey);
            if (is_callable($resolver)) {
                $result = $resolver($root, $args, $context, $info);
            } else {
                $result = $root[$fieldName] ?? null;
            }

            return $result;
        };
    }
}
