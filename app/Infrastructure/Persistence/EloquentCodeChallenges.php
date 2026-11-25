<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Customer\CodeChallenge;
use App\Domain\Customer\CodeChallenges;
use App\Domain\Customer\CodePolicy;
use App\Domain\Customer\PhoneNumber;
use App\Infrastructure\Persistence\Eloquent\OneTimeCodeRecord;
use DateTimeImmutable;

/**
 * The codes sent to phones, kept as hashes in `one_time_codes`. Only the latest code of a phone can
 * be used, so a new code replaces the ones before it.
 */
final readonly class EloquentCodeChallenges implements CodeChallenges
{
    /**
     * Keeps the new code.
     */
    public function open(PhoneNumber $phone, string $codeHash, DateTimeImmutable $issuedAt): void
    {
        $record = new OneTimeCodeRecord;
        $record->forceFill([
            'phone' => $phone->value,
            'code_hash' => $codeHash,
            'attempts' => 0,
            'expires_at' => CodePolicy::expiryOf($issuedAt),
            'created_at' => $issuedAt,
        ])->save();
    }

    /**
     * The latest code of the phone, unless it was used.
     */
    public function latest(PhoneNumber $phone): ?CodeChallenge
    {
        $record = OneTimeCodeRecord::query()->where('phone', $phone->value)->orderByDesc('id')->first();

        if (
            $record === null
            || $record->used_at !== null
        ) {
            return null;
        }

        return new CodeChallenge($record->id, $record->code_hash, $record->attempts, $record->expires_at);
    }

    /**
     * Counts one wrong guess.
     */
    public function recordAttempt(CodeChallenge $challenge): void
    {
        OneTimeCodeRecord::query()->whereKey($challenge->id)->increment('attempts');
    }

    /**
     * Marks the code used.
     */
    public function close(CodeChallenge $challenge, DateTimeImmutable $usedAt): void
    {
        OneTimeCodeRecord::query()->whereKey($challenge->id)->update(['used_at' => $usedAt]);
    }

    /**
     * How many codes the phone received since the moment.
     */
    public function countSince(PhoneNumber $phone, DateTimeImmutable $since): int
    {
        return OneTimeCodeRecord::query()->where('phone', $phone->value)->where('created_at', '>', $since)->count();
    }
}
