<?php

declare(strict_types=1);

namespace App\Domain\Customer;

use App\Domain\Shared\DomainError;

/**
 * A code did not open the account: wrong, expired, used or out of attempts. The same error for all,
 * so a guess learns nothing.
 */
final class CodeRefused extends DomainError
{
    /**
     * The error.
     */
    public static function create(): self
    {
        return new self('The code is wrong, expired or used up; ask for a new one.');
    }
}
