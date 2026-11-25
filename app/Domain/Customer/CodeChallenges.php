<?php

declare(strict_types=1);

namespace App\Domain\Customer;

use DateTimeImmutable;

/**
 * The codes sent to each phone (port; implemented in Infrastructure).
 */
interface CodeChallenges
{
    /**
     * Keeps a new code for the phone; the codes sent to it before stop working.
     */
    public function open(PhoneNumber $phone, string $codeHash, DateTimeImmutable $issuedAt): void;

    /**
     * The latest unused code of the phone, if any.
     */
    public function latest(PhoneNumber $phone): ?CodeChallenge;

    /**
     * Counts one wrong guess against the code.
     */
    public function recordAttempt(CodeChallenge $challenge): void;

    /**
     * Marks the code used, so it opens nothing again.
     */
    public function close(CodeChallenge $challenge, DateTimeImmutable $usedAt): void;

    /**
     * How many codes the phone received since the moment.
     */
    public function countSince(PhoneNumber $phone, DateTimeImmutable $since): int;
}
