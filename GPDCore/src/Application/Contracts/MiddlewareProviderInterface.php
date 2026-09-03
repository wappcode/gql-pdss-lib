<?php

namespace GPDCore\Application\Contracts;



interface MiddlewareProviderInterface
{
    public function registerMiddleware(MiddlewareQueueInterface $queue, AppContextInterface $context): void;
}
