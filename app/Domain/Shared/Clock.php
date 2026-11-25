<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use DateTimeImmutable;

/**
 * Tells the time, so the use cases that depend on it can be tested at any moment (port).
 */
interface Clock
{
    /**
     * Now.
     */
    public function now(): DateTimeImmutable;
}
