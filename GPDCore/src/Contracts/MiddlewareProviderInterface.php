<?php

namespace GPDCore\Contracts;



interface MiddlewareProviderInterface
{
    public function registerMiddleware(MiddlewareQueueInterface $queue, AppContextInterface $context): void;
}
