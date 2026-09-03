<?php

namespace GPDCore\Application\Contracts;

interface ConfigProviderInterface
{
    public function registerConfig(AppConfigInterface $config, AppContextInterface $context): void;
}
