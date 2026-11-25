<?php

declare(strict_types=1);

namespace App\Domain\Customer;

use App\Domain\Shared\DomainError;

/**
 * A value the app sent for a customer is not valid; the field says which.
 */
final class InvalidCustomerData extends DomainError
{
    /**
     * Holds the field and the message; use {@see InvalidCustomerData::of()} to build one.
     */
    private function __construct(
        public readonly CustomerField $field,
        string $message,
    ) {
        parent::__construct($message);
    }

    /**
     * The error for one field.
     */
    public static function of(CustomerField $field, string $reason): self
    {
        return new self($field, "Invalid {$field->value}: {$reason}.");
    }
}
