<?php

declare(strict_types=1);

namespace GPDCore\Graphql\Types;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use GraphQL\Error\Error;
use GraphQL\Utils\Utils;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Language\AST\StringValueNode;

final class DateTimeImmutableType extends ScalarType
{
    public function parseLiteral($valueNode, array $variables = null)
    {
        // Note: throwing GraphQL\Error\Error vs \UnexpectedValueException to benefit from GraphQL
        // error location in query:
        if (!($valueNode instanceof StringValueNode)) {
            throw new Error('Query error: Can only parse strings got: ' . $valueNode->kind, $valueNode);
        }

        return $this->parseValue($valueNode->value);
    }

    public function parseValue($value, array $variables = null)
    {
        if (!is_string($value)) {
            throw new \UnexpectedValueException('Cannot represent value as DateTime date: ' . Utils::printSafe($value));
        }
        $date = new DateTimeImmutable($value);
        $dateZone = date_default_timezone_get();
        if (!($dateZone instanceof DateTimeZone)) {
            $dateZone = new DateTimeZone($dateZone);
        }
        $date->setTimezone($dateZone);

        return $date;
    }

    public function serialize($value)
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('c');
        }

        return $value;
    }
}
