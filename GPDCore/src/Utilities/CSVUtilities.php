<?php

declare(strict_types=1);

namespace GPDCore\Utilities;

use GPDCore\Shared\CSVUtilities as Impl;

/**
 * Compatibility shim: original class FQCN preserved.
 * Remove the shim only in a major release after consumers migrate.
 */
class CSVUtilities extends Impl
{
    // intentionally empty — keeps old FQCN and behaviour via inheritance
}
