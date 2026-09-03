<?php

namespace GPDCore\Application\Contracts;

interface ResolverProviderInterface
{
    public function registerResolvers(ResolverManagerInterface $resolverManager, AppContextInterface $context): void;
}
