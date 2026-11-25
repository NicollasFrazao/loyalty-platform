<?php

declare(strict_types=1);

namespace App\Domain\Tenancy;

use App\Domain\Shared\DomainError;

/**
 * Another staff member, of any company, signs in with the email address.
 */
final class EmailTaken extends DomainError
{
    /**
     * The error, without the address.
     */
    public static function create(): self
    {
        return new self('A staff member already signs in with this email address.');
    }
}
