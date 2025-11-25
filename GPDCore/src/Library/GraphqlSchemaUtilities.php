<?php

namespace GPDCore\Library;

class GraphqlSchemaUtilities
{
    public static function extractQueryBody(string $query): string
    {
        $matches = [];
        preg_match('/type\s+Query\s+\{([^\{]*)\}/s', $query, $matches);

        return isset($matches[1]) ? trim($matches[1]) : '';
    }

    public static function extractMutationBody(string $query): string
    {
        $matches = [];
        preg_match('/type\s+Mutation\s+\{([^\{]*)\}/s', $query, $matches);

        return isset($matches[1]) ? trim($matches[1]) : '';
    }

    public static function extractTypes(string $schema): string
    {
        $query = static::extractQueryBody($schema);
        $mutation = static::extractMutationBody($schema);

        $types = str_replace([$query, $mutation], '', $schema);
        $types = preg_replace(["/type\s+Query\s+\{\s+\}/", "/type\s+Mutation\s+\{\s+\}/"], '', $types);
        $types = trim($types);

        return $types;
    }

    public static function combineSchemas(array $schemas): string
    {
        $queryFields = array_reduce($schemas, function (string $acc, string $schema) {
            $contentField = static::extractQueryBody($schema);

            return $acc . PHP_EOL . $contentField;
        }, '');
        $mutationFields = array_reduce($schemas, function (string $acc, string $schema) {
            $contentField = static::extractMutationBody($schema);

            return $acc . PHP_EOL . $contentField;
        }, '');
        $types = array_reduce($schemas, function (string $acc, string $schema) {
            $type = static::extractTypes($schema);

            return $acc . PHP_EOL . $type;
        }, '');
        $combinedSchemas = '';
        if (!empty(trim($queryFields))) {
            $combinedSchemas .= 'type Query {' . PHP_EOL . $queryFields . PHP_EOL . '}' . PHP_EOL;
        }
        if (!empty(trim($mutationFields))) {
            $combinedSchemas .= 'type Mutation {' . PHP_EOL . $mutationFields . PHP_EOL . '}' . PHP_EOL;
        }
        $combinedSchemas .= $types;

        return $combinedSchemas;
    }
}
