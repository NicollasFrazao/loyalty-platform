<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared;

use App\Domain\Shared\Clock;
use Carbon\CarbonImmutable;

/**
 * The real time, through Carbon so a feature test can travel in it.
 */
final readonly class SystemClock implements Clock
{
    /**
     * Now.
     */
    public function now(): CarbonImmutable
    {
        return CarbonImmutable::now();
    }
}
