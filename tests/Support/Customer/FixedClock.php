<?php

declare(strict_types=1);

namespace Tests\Support\Customer;

use App\Domain\Shared\Clock;
use DateInterval;
use DateTimeImmutable;

/**
 * A clock that stands still until the test moves it.
 */
final class FixedClock implements Clock
{
    /**
     * Starts at the moment.
     */
    public function __construct(private DateTimeImmutable $now = new DateTimeImmutable('2026-11-25 12:00:00')) {}

    /**
     * The moment.
     */
    public function now(): DateTimeImmutable
    {
        return $this->now;
    }

    /**
     * Moves forward by an ISO 8601 duration, such as "PT5M".
     */
    public function advance(string $duration): void
    {
        $this->now = $this->now->add(new DateInterval($duration));
    }
}
