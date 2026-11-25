<?php

declare(strict_types=1);

namespace App\Domain\CheckIn;

use App\Domain\Shared\DomainError;

/**
 * A staff member tried to confirm or reject a check-in that is no longer pending.
 */
final class CheckInDecided extends DomainError
{
    /**
     * The error for the check-in.
     */
    public static function already(CheckIn $checkIn): self
    {
        return new self("The check-in {$checkIn->id->value} is {$checkIn->status->value} already.");
    }
}
