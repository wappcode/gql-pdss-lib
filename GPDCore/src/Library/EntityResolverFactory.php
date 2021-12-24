<?php 

namespace GPDCore\Library;

use GraphQL\Deferred;
use GraphQL\Type\Definition\ResolveInfo;

class EntityResolverFactory {
    public static function createResolver(EntityBuffer $buffer, string $property) {
        return function ($source, array $args, $context, ResolveInfo $info) use($buffer, $property) {
            $id = $source[$property]['id'] ?? 0;
            $buffer->add($id);
            return new Deferred(function () use ($id, $source, $args, $context, $info, $buffer) {
                $buffer->loadBuffered($source, $args, $context, $info);
                $result = $buffer->get($id);
                return $result;
            });
        };
    }
}