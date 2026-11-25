<?php

declare(strict_types=1);

namespace App\Domain\Tenancy;

use App\Domain\Shared\DomainError;

/**
 * A value typed for a staff member is not valid; the field says which.
 */
final class InvalidStaffData extends DomainError
{
    /**
     * Holds the field and the message; use {@see InvalidStaffData::of()} to build one.
     */
    private function __construct(
        public readonly StaffField $field,
        string $message,
    ) {
        parent::__construct($message);
    }

    /**
     * The error for one field.
     */
    public static function of(StaffField $field, string $reason): self
    {
        return new self($field, "Invalid {$field->value}: {$reason}.");
    }
}
