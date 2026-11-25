<?php

declare(strict_types=1);

namespace App\Domain\Customer;

use App\Domain\Shared\DomainError;

/**
 * A phone asked for more codes than the policy allows in its window.
 */
final class TooManyCodes extends DomainError
{
    /**
     * The error.
     */
    public static function create(): self
    {
        return new self('This phone asked for too many codes; wait a few minutes.');
    }
}
