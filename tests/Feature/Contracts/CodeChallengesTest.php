<?php

declare(strict_types=1);

use App\Domain\Customer\CodeChallenge;
use App\Domain\Customer\CodeChallenges;
use App\Domain\Customer\PhoneNumber;
use App\Infrastructure\Persistence\EloquentCodeChallenges;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\Customer\InMemoryCodeChallenges;

uses(RefreshDatabase::class);

/*
 * The contract every CodeChallenges keeps. The in-memory adapter is the reference; the Eloquent
 * adapter runs it on MySQL.
 */
dataset('code challenges', [
    'in memory' => fn (): CodeChallenges => new InMemoryCodeChallenges,
    'eloquent' => fn (): CodeChallenges => new EloquentCodeChallenges,
]);

it('offers only the latest code of a phone, until it is used', function (CodeChallenges $challenges): void {
    $phone = PhoneNumber::from('+5511912345678');
    $issuedAt = new DateTimeImmutable('2026-11-25 12:00:00');
    $challenges->open($phone, 'first', $issuedAt);
    $challenges->open($phone, 'second', $issuedAt->modify('+1 minute'));
    $challenges->open(PhoneNumber::from('+5521987654321'), 'other', $issuedAt);

    $latest = $challenges->latest($phone);

    expect($latest?->codeHash)->toBe('second')
        ->and($latest?->attempts)->toBe(0)
        ->and($latest?->expiresAt)->toEqual(new DateTimeImmutable('2026-11-25 12:06:00'));

    $challenges->close($latest ?? throw new LogicException('No code.'), $issuedAt->modify('+2 minutes'));

    expect($challenges->latest($phone))->toBeNull()
        ->and($challenges->latest(PhoneNumber::from('+5531900000000')))->toBeNull();
})->with('code challenges');

it('counts wrong guesses and the codes of a window', function (CodeChallenges $challenges): void {
    $phone = PhoneNumber::from('+5511912345678');
    $start = new DateTimeImmutable('2026-11-25 12:00:00');
    $challenges->open($phone, 'first', $start);
    $challenges->open($phone, 'second', $start->modify('+10 minutes'));
    $latest = $challenges->latest($phone) ?? throw new LogicException('No code.');

    $challenges->recordAttempt($latest);
    $challenges->recordAttempt($latest);

    expect($challenges->latest($phone)?->attempts)->toBe(2)
        ->and($challenges->countSince($phone, $start->modify('-1 second')))->toBe(2)
        ->and($challenges->countSince($phone, $start))->toBe(1)
        ->and($challenges->countSince(PhoneNumber::from('+5521987654321'), $start->modify('-1 day')))->toBe(0);
})->with('code challenges');

it('keeps no code, only its hash', function (): void {
    $challenges = new EloquentCodeChallenges;
    $challenges->open(PhoneNumber::from('+5511912345678'), 'the hash', new DateTimeImmutable);

    expect($challenges->latest(PhoneNumber::from('+5511912345678')))->toBeInstanceOf(CodeChallenge::class)
        ->and(DB::table('one_time_codes')->value('code_hash'))->toBe('the hash');
});
