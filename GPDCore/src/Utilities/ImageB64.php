<?php

declare(strict_types=1);

namespace GPDCore\Utilities;

use GPDCore\Shared\ImageB64 as Impl;

/**
 * Compatibility shim: original class FQCN preserved.
 * Remove the shim only in a major release after consumers migrate.
 */
class ImageB64 extends Impl
{
    // intentionally empty — keeps old FQCN and behaviour via inheritance
}
