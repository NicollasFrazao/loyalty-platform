<?php

declare(strict_types=1);

namespace App\Domain\Tenancy;

use App\Domain\Shared\DomainError;

/**
 * A sign-in failed; the same error for an unknown email and a wrong password.
 */
final class InvalidCredentials extends DomainError
{
    /**
     * The error, without saying which was wrong.
     */
    public static function create(): self
    {
        return new self('The email or the password is wrong.');
    }
}
