<?php

declare(strict_types=1);

namespace App\Domain\Customer;

use SensitiveParameter;

/**
 * A code just made: the digits to send to the phone, and the hash to keep instead of them.
 */
final readonly class IssuedCode
{
    /**
     * Pairs the code with its hash.
     */
    public function __construct(
        #[SensitiveParameter] public string $code,
        public string $hash,
    ) {}
}
