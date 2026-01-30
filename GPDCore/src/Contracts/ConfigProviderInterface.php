<?php

namespace GPDCore\Contracts;

interface ConfigProviderInterface
{
    public function registerConfig(AppConfigInterface $config, AppContextInterface $context): void;
}
