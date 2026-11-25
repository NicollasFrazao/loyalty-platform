<?php

declare(strict_types=1);

namespace App\Domain\CheckIn;

use App\Domain\Shared\DomainError;

/**
 * No check-in of the company has the id.
 */
final class CheckInNotFound extends DomainError
{
    /**
     * The error for the id.
     */
    public static function withId(CheckInId $id): self
    {
        return new self("No check-in has the id {$id->value}.");
    }
}
