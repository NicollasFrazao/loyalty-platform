<?php

declare(strict_types=1);

namespace App\Domain\Customer;

use SensitiveParameter;

/**
 * Makes one-time codes and checks a typed code against a kept hash (port; implemented in
 * Infrastructure with a random source and a keyed hash).
 */
interface OneTimeCodes
{
    /**
     * A new six-digit code with its hash.
     */
    public function issue(): IssuedCode;

    /**
     * Tells whether the typed code is the one the hash was made from.
     */
    public function matches(#[SensitiveParameter] string $typed, string $hash): bool;
}
