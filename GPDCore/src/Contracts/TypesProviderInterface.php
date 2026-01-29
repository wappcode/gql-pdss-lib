<?php

namespace GPDCore\Contracts;

use GPDCore\Core\TypesManager;




interface TypesProviderInterface
{
    public function registerType(TypesManager $typesManager, AppContextInterface $context): void;
}
