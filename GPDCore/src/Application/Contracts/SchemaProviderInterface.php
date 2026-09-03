<?php

namespace GPDCore\Application\Contracts;

use GPDCore\Application\Internal\SchemaManager;

interface SchemaProviderInterface
{
    public function registerSchemaChunk(SchemaManager $schemaManager, AppContextInterface $context): void;
}
