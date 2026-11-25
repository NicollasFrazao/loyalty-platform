<?php

declare(strict_types=1);

use App\Domain\Customer\CodeChallenge;
use App\Domain\Customer\CodePolicy;
use App\Domain\Customer\CustomerField;
use App\Domain\Customer\CustomerProfile;
use App\Domain\Customer\Document;
use App\Domain\Customer\InvalidCustomerData;
use App\Domain\Customer\PhoneNumber;

/**
 * The field the call refuses, or null when it accepts the value.
 *
 * @param  Closure(): mixed  $call  Reads a value.
 */
function refusedField(Closure $call): ?CustomerField
{
    try {
        $call();
    } catch (InvalidCustomerData $error) {
        return $error->field;
    }

    return null;
}

it('reads a phone typed with separators, and refuses one without the country', function (): void {
    expect(PhoneNumber::from(' +55 (11) 91234-5678 ')->value)->toBe('+5511912345678')
        ->and(static fn (): PhoneNumber => PhoneNumber::from('11912345678'))->toThrow(InvalidCustomerData::class, 'Invalid phone')
        ->and(static fn (): PhoneNumber => PhoneNumber::from('+0511912345678'))->toThrow(InvalidCustomerData::class)
        ->and(static fn (): PhoneNumber => PhoneNumber::from('+55119'))->toThrow(InvalidCustomerData::class);
});

it('accepts a CPF whose check digits match, with or without punctuation', function (): void {
    expect(Document::from('529.982.247-25')->digits)->toBe('52998224725')
        ->and(Document::from('11144477735')->digits)->toBe('11144477735');
});

it('refuses a CPF with wrong check digits, the wrong length or one repeated digit', function (string $cpf): void {
    expect(refusedField(static fn (): Document => Document::from($cpf)))->toBe(CustomerField::Document);
})->with(['52998224724', '5299822472', '11111111111', 'abc.def.ghi-jk']);

it('reads a profile as typed, leaving out what is empty', function (): void {
    $today = new DateTimeImmutable('2026-11-25 15:00');

    $full = CustomerProfile::from(' Maria Souza ', '1990-05-17', '529.982.247-25', $today);
    $empty = CustomerProfile::from('', ' ', '', $today);

    expect($full->name?->value)->toBe('Maria Souza')
        ->and($full->birthDate?->format('Y-m-d'))->toBe('1990-05-17')
        ->and($full->document?->digits)->toBe('52998224725')
        ->and($empty)->toEqual(new CustomerProfile);
});

it('refuses a birth date that is not a past calendar day', function (string $date): void {
    expect(refusedField(static fn (): CustomerProfile => CustomerProfile::from('', $date, '', new DateTimeImmutable('2026-11-25 15:00'))))
        ->toBe(CustomerField::BirthDate);
})->with(['2026-11-25', '2030-01-01', '1990-02-30', '17/05/1990']);

it('refuses a name that is too long', function (): void {
    expect(static fn (): CustomerProfile => CustomerProfile::from(str_repeat('a', 101), '', '', new DateTimeImmutable))
        ->toThrow(InvalidCustomerData::class, 'Invalid name');
});

it('keeps a code open for five minutes and five wrong guesses', function (): void {
    $issuedAt = new DateTimeImmutable('2026-11-25 12:00:00');
    $code = new CodeChallenge(1, 'hash', 0, CodePolicy::expiryOf($issuedAt));

    expect($code->isOpenAt($issuedAt->modify('+4 minutes 59 seconds')))->toBeTrue()
        ->and($code->isOpenAt($issuedAt->modify('+5 minutes')))->toBeFalse()
        ->and(new CodeChallenge(1, 'hash', CodePolicy::MAX_ATTEMPTS - 1, CodePolicy::expiryOf($issuedAt))->isOpenAt($issuedAt))->toBeTrue()
        ->and(new CodeChallenge(1, 'hash', CodePolicy::MAX_ATTEMPTS, CodePolicy::expiryOf($issuedAt))->isOpenAt($issuedAt))->toBeFalse()
        ->and(CodePolicy::windowStart($issuedAt))->toEqual(new DateTimeImmutable('2026-11-25 11:45:00'));
});
