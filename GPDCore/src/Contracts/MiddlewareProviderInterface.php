<?php

namespace GPDCore\Contracts;

use GPDCore\Core\MiddlewareQueue;


interface MiddlewareProviderInterface
{
    public function registerMiddleware(MiddlewareQueue $queue, AppContextInterface $context): void;
}
