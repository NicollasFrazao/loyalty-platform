<?php

declare(strict_types=1);

namespace App\Infrastructure\Customer;

use App\Domain\Customer\IssuedCode;
use App\Domain\Customer\OneTimeCodes;
use SensitiveParameter;

/**
 * Six-digit codes from the system's secure random source, kept as an HMAC-SHA256 under the
 * application key: a leaked table alone does not give the codes away.
 */
final readonly class HmacOneTimeCodes implements OneTimeCodes
{
    /**
     * The digits of a code.
     */
    private const int DIGITS = 6;

    /**
     * The largest code: six nines.
     */
    private const int LARGEST = 999_999;

    /**
     * Takes the key the hashes are made with.
     */
    public function __construct(#[SensitiveParameter] private string $key) {}

    /**
     * A new code with its hash.
     */
    public function issue(): IssuedCode
    {
        $code = str_pad((string) random_int(0, self::LARGEST), self::DIGITS, '0', STR_PAD_LEFT);

        return new IssuedCode($code, $this->hash($code));
    }

    /**
     * Compares the hashes in constant time.
     */
    public function matches(#[SensitiveParameter] string $typed, string $hash): bool
    {
        return hash_equals($hash, $this->hash($typed));
    }

    /**
     * The keyed hash of a code.
     */
    private function hash(#[SensitiveParameter] string $code): string
    {
        return hash_hmac('sha256', $code, $this->key);
    }
}
