<?php

declare(strict_types=1);

namespace App\Application\Customer;

/**
 * A profile as the app sends it; an empty text leaves the value out.
 */
final readonly class ProfileInput
{
    /**
     * Holds the values as typed.
     */
    public function __construct(
        public string $name,
        public string $birthDate,
        public string $document,
    ) {}
}
