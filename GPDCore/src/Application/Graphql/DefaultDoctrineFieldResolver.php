<?php

declare(strict_types=1);

namespace GPDCore\Application\Graphql;

use Closure;
use Exception;
use GraphQL\Type\Definition\ResolveInfo;
use ReflectionClass;
use ReflectionMethod;

final class DefaultDoctrineFieldResolver
{
    public function __invoke($source, array $args, $context, ResolveInfo $info)
    {
        $fieldName = $info->fieldName;
        $property = null;

        if (is_object($source)) {
            $property = $this->resolveGQL($source, $args, $context, $info);
            if ($property === null) {
                $property = $this->resolveObject($source, $args, $fieldName);
            }
        } elseif (is_array($source)) {
            $property = $this->resolveArray($source, $fieldName);
        }

        return $property instanceof Closure ? $property($source, $args, $context) : $property;
    }

    private function resolveGQL($source, array $args, $context, ResolveInfo $info)
    {
        $fieldName = $info->fieldName;
        $resolver = $this->getResolver($source, $fieldName);
        if ($resolver) {
            $resolveClass = $this->getResolveClass($source);
            $resolveObj = new $resolveClass();
            $args = [$source, $args, $context, $info];

            return $resolver->invoke($resolveObj, ...$args);
        }

        return null;
    }

    private function resolveObject($source, array $args, string $fieldName)
    {
        $getter = $this->getGetter($source, $fieldName);
        if ($getter) {
            $args = $this->orderArguments($getter, $args);

            return $getter->invoke($source, ...$args);
        }

        if (isset($source->{$fieldName})) {
            return $source->{$fieldName};
        }

        return null;
    }

    private function resolveArray($source, string $fieldName)
    {
        return $source[$fieldName] ?? null;
    }

    private function getGetter($source, string $name): ?ReflectionMethod
    {
        if (!preg_match('~^(is|has)[A-Z]~', $name)) {
            $name = 'get' . ucfirst($name);
        }

        $class = new ReflectionClass($source);
        if ($class->hasMethod($name)) {
            $method = $class->getMethod($name);
            if ($method->getModifiers() & ReflectionMethod::IS_PUBLIC) {
                return $method;
            }
        }

        return null;
    }

    private function getResolver($source, string $name): ?ReflectionMethod
    {
        $resolveClass = $this->getResolveClass($source);
        if ($resolveClass === null) {
            return null;
        }
        if (!preg_match('~^(is|has)[A-Z]~', $name)) {
            $name = 'resolve' . ucfirst($name);
        }

        try {
            $class = new ReflectionClass($resolveClass);
            if ($class->hasMethod($name)) {
                $method = $class->getMethod($name);
                if ($method->getModifiers() & ReflectionMethod::IS_PUBLIC) {
                    return $method;
                }
            }
        } catch (Exception $e) {
            return null;
        }

        return null;
    }

    private function getResolveClass($source)
    {
        $className = is_object($source) ? get_class($source) : $source;
        $className = str_replace('DoctrineProxies\__CG__', '', $className);
        if (!is_string($className)) {
            return null;
        }
        $resolveClass = sprintf('%sGQLResolve', $className);

        return $resolveClass;
    }

    private function orderArguments(ReflectionMethod $method, array $args): array
    {
        $result = [];
        if (!$args) {
            return $result;
        }

        foreach ($method->getParameters() as $param) {
            if (array_key_exists($param->getName(), $args)) {
                $arg = $args[$param->getName()];

                $result[] = $arg;
            }
        }

        return $result;
    }
}
