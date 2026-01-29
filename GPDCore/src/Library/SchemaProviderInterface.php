<?php

namespace GPDCore\Library;



interface SchemaProviderInterface
{
    public function registerSchemaChunk(SchemaManager $schemaManager, AppContextInterface $context): void;
}
