<?php

declare(strict_types=1);

namespace App\Domain\Customer;

use App\Domain\Shared\DomainError;

/**
 * No customer has the id, or the account was deleted.
 */
final class CustomerNotFound extends DomainError
{
    /**
     * The error for the id.
     */
    public static function withId(CustomerId $id): self
    {
        return new self("No customer has the id {$id->value}.");
    }
}
