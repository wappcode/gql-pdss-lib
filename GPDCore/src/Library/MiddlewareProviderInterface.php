<?php

namespace GPDCore\Library;

interface MiddlewareProviderInterface
{
    public function registerMiddleware(MiddlewareQueue $queue, AppContextInterface $context): void;
}
