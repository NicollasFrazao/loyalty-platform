<?php

declare(strict_types=1);

namespace App\Domain\CheckIn;

use DateTimeImmutable;
use DateTimeInterface;

/**
 * The customer checked in at the company within the cooldown.
 */
final class CheckInTooSoon extends CheckInRefused
{
    /**
     * Holds the moment the next check-in is accepted; use {@see CheckInTooSoon::until()}.
     */
    private function __construct(public readonly DateTimeImmutable $nextAllowedAt)
    {
        parent::__construct('You checked in here recently; the next check-in opens at '.$nextAllowedAt->format(DateTimeInterface::ATOM).'.');
    }

    /**
     * The error, with the moment the cooldown ends.
     */
    public static function until(DateTimeImmutable $nextAllowedAt): self
    {
        return new self($nextAllowedAt);
    }
}
