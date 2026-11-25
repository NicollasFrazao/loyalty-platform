<?php

declare(strict_types=1);

namespace App\Domain\Tenancy;

use SensitiveParameter;

/**
 * Hashes passwords and checks a typed password against a hash (port; implemented in Infrastructure).
 */
interface PasswordHasher
{
    /**
     * The hash of a new password.
     */
    public function hash(PlainPassword $password): HashedPassword;

    /**
     * Tells whether the typed text is the password of the hash.
     */
    public function matches(#[SensitiveParameter] string $typed, HashedPassword $hash): bool;

    /**
     * Checks the typed text against a hash no password matches, so an unknown email takes as long as
     * a wrong password.
     */
    public function spendCheck(#[SensitiveParameter] string $typed): void;
}
