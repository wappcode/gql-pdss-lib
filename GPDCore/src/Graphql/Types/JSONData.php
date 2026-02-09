<?php

declare(strict_types=1);

namespace GPDCore\Graphql\Types;

use Exception;
use GraphQL\Error\Error;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Utils\Utils;
use UnexpectedValueException;

final class JSONData extends ScalarType
{
    public const NAME = 'JSONData';

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->name = static::NAME;
        $this->description = 'Parse a string to an array';
    }

    public function parseLiteral($valueNode, ?array $variables = null)
    {
        // Note: throwing GraphQL\Error\Error vs \UnexpectedValueException to benefit from GraphQL
        // error location in query:
        if (!($valueNode instanceof StringValueNode)) {
            throw new Error('Query error: Can only parse strings got: ' . $valueNode->kind, $valueNode);
        }

        return $this->parseValue($valueNode->value);
    }

    public function parseValue($value, ?array $variables = null)
    {
        if (!is_string($value)) {
            throw new UnexpectedValueException('Cannot represent value as an object: ' . Utils::printSafe($value));
        }

        try {
            $json = json_decode($value, true);

            return $json;
        } catch (Exception $e) {
            throw new Error('Invalid JSON');
        }
    }

    public function serialize($value)
    {
        try {
            return json_encode($value);
        } catch (Exception $e) {
            throw new Error('Invalid JSON Object Value');
        }
    }
}
