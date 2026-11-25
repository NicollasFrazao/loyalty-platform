<?php

declare(strict_types=1);

namespace App\Domain\Level;

use App\Domain\Shared\DomainError;

/**
 * A company's levels do not form a ladder: the reason says what to fix.
 */
final class InvalidLadder extends DomainError
{
    /**
     * The error with its reason.
     */
    public static function because(string $reason): self
    {
        return new self("The levels are not valid: {$reason}.");
    }
}
