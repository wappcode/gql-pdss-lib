<?php

namespace GPDCore\Contracts;

interface ResolverProviderInterface
{
    public function registerResolvers(ResolverManagerInterface $resolverManager, AppContextInterface $context): void;
}
