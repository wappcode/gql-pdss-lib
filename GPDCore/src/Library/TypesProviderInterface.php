<?php

namespace GPDCore\Library;



interface TypesProviderInterface
{
    public function registerType(TypesManager $typesManager, AppContextInterface $context): void;
}
