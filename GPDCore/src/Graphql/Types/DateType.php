<?php

declare(strict_types=1);

namespace GPDCore\Graphql\Types;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use GraphQL\Error\Error;
use GraphQL\Utils\Utils;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Language\AST\StringValueNode;

final class DateType extends ScalarType
{
    public function __construct(array $config = [])
    {
        $config["name"] = "Date";
        parent::__construct($config);
    }
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
        $valueTrim = trim($value);
        if (!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $valueTrim)) {
            throw new Error('Invalid date format (YYYY-MM-dd)');
        }
        $date = new DateTime($valueTrim);
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
            return $value->format('Y-m-d');
        }

        return $value;
    }
}
