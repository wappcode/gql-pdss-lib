<?php

namespace GPDCore\Application\Contracts;

use GPDCore\Application\Internal\TypesManager;

interface TypesProviderInterface
{
    public function registerType(TypesManager $typesManager, AppContextInterface $context): void;
}
