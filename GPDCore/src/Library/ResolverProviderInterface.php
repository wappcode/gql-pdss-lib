<?php

namespace GPDCore\Library;


interface ResolverProviderInterface
{
    public function registerResolvers(ResolverManagerInterface $resolverManager, AppContextInterface $context): void;
}
