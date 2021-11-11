<?php

declare(strict_types=1);

namespace GPDCore\Graphql;

use GPDCore\Graphql\ResolverManager;
use GraphQL\Deferred;
use GraphQL\Type\Definition\ResolveInfo;



/**
 * A field resolver that will allow access to public properties and getter.
 * Arguments, if any, will be forwarded as is to the method.
 */
final class DefaultArrayResolver
{
    /**
     * @param mixed $source
     * @param mixed[] $args
     * @param mixed $context
     * @param ResolveInfo $info
     *
     * @return null|mixed
     */
    public function __invoke($source, array $args, $context, ResolveInfo $info)
    {
        /** @var string $fieldName */
        $fieldName = $info->fieldName;
        $resolverKey = sprintf("%s::%s",$info->parentType->name,$fieldName);
        $resolver = ResolverManager::get($resolverKey);
        if(is_callable($resolver)) {
            $result = $resolver($source, $args, $context, $info);
        } else {
            $result = $source[$fieldName] ?? null;
        }
        return $result;
    }

}

