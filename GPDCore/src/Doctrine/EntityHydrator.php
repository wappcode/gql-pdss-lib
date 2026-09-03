<?php

declare(strict_types=1);

namespace GPDCore\Doctrine;

use GPDCore\Infrastructure\Doctrine\EntityHydrator as Impl;

/**
 * Compatibility shim: original class FQCN preserved.
 * Remove the shim only in a major release after consumers migrate.
 */
class EntityHydrator extends Impl
{
    // intentionally empty — keeps old FQCN and behaviour via inheritance
}
