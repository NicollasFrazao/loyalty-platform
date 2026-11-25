<?php

declare(strict_types=1);

namespace App\Domain\CheckIn;

use App\Domain\Shared\DomainError;

/**
 * A value of a check-in or of the check-in settings is not valid; the field says which.
 */
final class InvalidCheckInSettings extends DomainError
{
    /**
     * Holds the field and the message; use {@see InvalidCheckInSettings::of()} to build one.
     */
    private function __construct(
        public readonly CheckInField $field,
        string $message,
    ) {
        parent::__construct($message);
    }

    /**
     * The error for one field.
     */
    public static function of(CheckInField $field, string $expected): self
    {
        return new self($field, "The {$field->value} must be {$expected}.");
    }
}
