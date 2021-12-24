<?php 

namespace GPDCore\Library;

use GraphQL\Deferred;
use GraphQL\Type\Definition\ResolveInfo;

class ResolverFactory {

    protected static $buffers = [];
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
    public static function createCollectionResolver(string $mainClass, string $property, array $propertyRelations) {
        $key = sprintf("%s::%s", $mainClass, $property);
        if (static::$buffers[$key] === null) {
            static::$buffers[$key] = new CollectionBuffer($mainClass, $property, $propertyRelations);
        }
        $buffer = static::$buffers[$key];
        return function ($source, $args, $context, $info) use($buffer) {
            $id = $source['id'] ?? 0;
            $buffer->add($id);
            return new Deferred(function () use ($id, $source, $args, $context, $info, $buffer) {
                $buffer->loadBuffered($source, $args, $context, $info);
                $result = $buffer->get($id);
                return $result;
            });
        };

    }
}