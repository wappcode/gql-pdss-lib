<?php

namespace GPDCore\Core;

use GPDCore\Graphql\GraphqlSchemaUtilities;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;

class SchemaManager
{
    protected array $schemaChunks = [];

    public function add(string $chunk): void
    {
        $this->schemaChunks[] = $chunk;
    }

    public function buildSchema(?TypesManager $typesManager): Schema
    {
        $typedefinitions = function (array $typeConfig, TypeDefinitionNode $typeDefinitionNode) use ($typesManager) {
            $name = $typeConfig['name'];
            if ($typesManager != null && $typesManager->has($name)) {
                /** @var ScalarType */
                $type = $this->getType($typesManager, $name);
                if ($type instanceof ScalarType) {
                    $config = [
                        'serialize' => function ($value) use ($type) {
                            return $type->serialize($value);
                        },
                        'parseValue' => function ($value) use ($type) {
                            return $type->parseValue($value);
                        },
                        'parseLiteral' => function ($valueNode) use ($type) {
                            return $type->parseLiteral($valueNode);
                        },
                    ];

                    return array_merge($typeConfig, $config);
                }
            }

            return $typeConfig;
        };
        $schemaUtilities = file_get_contents(__DIR__ . '/../Assets/gql-pdss.graphqls');
        $allSchemas = [$schemaUtilities, ...$this->schemaChunks];
        $schemasContent = GraphqlSchemaUtilities::combineSchemas($allSchemas);
        $queryField = preg_match("/type\sQuery/", $schemasContent) ? 'query: Query' : '';
        $mutationField = preg_match("/type\sMutation/", $schemasContent) ? 'mutation: Mutation' : '';
        $schemaBase = "schema {
                {$queryField}
                {$mutationField}
             }
        ";
        $appSchema = $schemaBase . PHP_EOL . $schemasContent;

        $schema = BuildSchema::build($appSchema, $typedefinitions);

        return $schema;
    }

    private function getType(?TypesManager $typesManager, $name): ?ScalarType
    {
        $type = $typesManager->get($name);
        if ($type instanceof ScalarType) {
            return $type;
        }
        if (is_string($type)) {
            try {
                $typeInstance = new $type();
                if ($typeInstance instanceof ScalarType) {
                    return $typeInstance;
                }
            } catch (\Throwable $e) {
                return null;
            }
        }
        return null;
    }
}
