<?php

namespace GPDCore\Contracts;

use GPDCore\Core\SchemaManager;

interface SchemaProviderInterface
{
    public function registerSchemaChunk(SchemaManager $schemaManager, AppContextInterface $context): void;
}
