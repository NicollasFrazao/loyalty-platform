<?php

declare(strict_types=1);

namespace App\Domain\Customer;

use DateTimeImmutable;

/**
 * A code sent to a phone and not used yet: its hash, the wrong guesses so far and when it expires.
 */
final readonly class CodeChallenge
{
    /**
     * Describes the challenge.
     *
     * @param  int  $id  The storage's id of the challenge.
     * @param  string  $codeHash  The code's hash; the code itself is never kept.
     */
    public function __construct(
        public int $id,
        public string $codeHash,
        public int $attempts,
        public DateTimeImmutable $expiresAt,
    ) {}

    /**
     * Tells whether the code can still be tried at that moment.
     */
    public function isOpenAt(DateTimeImmutable $now): bool
    {
        return $now < $this->expiresAt && $this->attempts < CodePolicy::MAX_ATTEMPTS;
    }
}
