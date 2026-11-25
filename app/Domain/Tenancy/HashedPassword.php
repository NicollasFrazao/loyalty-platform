<?php

declare(strict_types=1);

namespace App\Domain\Tenancy;

/**
 * A password hash, the only form a password is kept in.
 */
final readonly class HashedPassword
{
    /**
     * Wraps the hash the {@see PasswordHasher} made.
     */
    public function __construct(public string $value) {}
}
