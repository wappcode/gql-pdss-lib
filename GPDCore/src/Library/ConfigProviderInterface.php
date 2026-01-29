<?php

namespace GPDCore\Library;


interface ConfigProviderInterface
{
    public function registerConfig(AppConfigInterface $config, AppContextInterface $context): void;
}
