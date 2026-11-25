<?php

declare(strict_types=1);

namespace Tests\Support\Customer;

use App\Domain\Customer\CodeChallenge;
use App\Domain\Customer\CodeChallenges;
use App\Domain\Customer\CodePolicy;
use App\Domain\Customer\PhoneNumber;
use DateTimeImmutable;
use LogicException;

/**
 * Keeps the codes in memory, in the order they were sent.
 */
final class InMemoryCodeChallenges implements CodeChallenges
{
    /**
     * Every code, keyed by id.
     *
     * @var array<int, array{phone: string, challenge: CodeChallenge, issuedAt: DateTimeImmutable, used: bool}>
     */
    private array $codes = [];

    /**
     * Keeps the code.
     */
    public function open(PhoneNumber $phone, string $codeHash, DateTimeImmutable $issuedAt): void
    {
        $id = count($this->codes) + 1;
        $this->codes[$id] = [
            'phone' => $phone->value,
            'challenge' => new CodeChallenge($id, $codeHash, 0, CodePolicy::expiryOf($issuedAt)),
            'issuedAt' => $issuedAt,
            'used' => false,
        ];
    }

    /**
     * The latest code of the phone, unless it was used.
     */
    public function latest(PhoneNumber $phone): ?CodeChallenge
    {
        $latest = null;

        foreach ($this->codes as $code) {
            if ($code['phone'] !== $phone->value) {
                continue;
            }

            $latest = $code;
        }

        return $latest === null || $latest['used'] ? null : $latest['challenge'];
    }

    /**
     * Counts one wrong guess.
     *
     * @throws LogicException when no code has the id.
     */
    public function recordAttempt(CodeChallenge $challenge): void
    {
        $kept = ($this->codes[$challenge->id] ?? throw new LogicException("No code has the id {$challenge->id}."))['challenge'];
        $this->codes[$challenge->id]['challenge'] = new CodeChallenge($kept->id, $kept->codeHash, $kept->attempts + 1, $kept->expiresAt);
    }

    /**
     * Marks the code used.
     */
    public function close(CodeChallenge $challenge, DateTimeImmutable $usedAt): void
    {
        $this->codes[$challenge->id]['used'] = true;
    }

    /**
     * The codes the phone received after the moment.
     */
    public function countSince(PhoneNumber $phone, DateTimeImmutable $since): int
    {
        return count(array_filter(
            $this->codes,
            static fn (array $code): bool => $code['phone'] === $phone->value && $code['issuedAt'] > $since,
        ));
    }
}
