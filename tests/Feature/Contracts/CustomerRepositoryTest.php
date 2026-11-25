<?php

declare(strict_types=1);

use App\Domain\Customer\Customer;
use App\Domain\Customer\CustomerId;
use App\Domain\Customer\CustomerNotFound;
use App\Domain\Customer\CustomerProfile;
use App\Domain\Customer\CustomerRepository;
use App\Domain\Customer\PhoneNumber;
use App\Infrastructure\Persistence\Eloquent\CustomerRecord;
use App\Infrastructure\Persistence\EloquentCustomerRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\Customer\InMemoryCustomerRepository;

uses(RefreshDatabase::class);

/*
 * The contract every CustomerRepository keeps. The in-memory adapter is the reference; the Eloquent
 * adapter runs it on MySQL.
 */
dataset('customer repositories', [
    'in memory' => fn (): CustomerRepository => new InMemoryCustomerRepository,
    'eloquent' => fn (): CustomerRepository => new EloquentCustomerRepository,
]);

it('registers a verified phone, finds it, and keeps its profile', function (CustomerRepository $customers): void {
    $verifiedAt = new DateTimeImmutable('2026-11-25 12:00:00');
    $id = $customers->register(PhoneNumber::from('+5511912345678'), $verifiedAt);
    $profile = CustomerProfile::from('Maria', '1990-05-17', '52998224725', $verifiedAt);

    $customers->saveProfile($id, $profile);

    expect($customers->findByPhone(PhoneNumber::from('+5511912345678')))->toEqual($id)
        ->and($customers->findByPhone(PhoneNumber::from('+5521987654321')))->toBeNull()
        ->and($customers->get($id))->toEqual(new Customer($id, PhoneNumber::from('+5511912345678'), $profile, $verifiedAt))
        ->and(static fn (): Customer => $customers->get(new CustomerId($id->value + 1)))->toThrow(CustomerNotFound::class);
})->with('customer repositories');

it('anonymises an account: it is never found again, and the phone is free', function (CustomerRepository $customers): void {
    $now = new DateTimeImmutable('2026-11-25 12:00:00');
    $id = $customers->register(PhoneNumber::from('+5511912345678'), $now);
    $customers->saveProfile($id, CustomerProfile::from('Maria', '1990-05-17', '52998224725', $now));

    $customers->anonymise($id, $now);

    expect($customers->findByPhone(PhoneNumber::from('+5511912345678')))->toBeNull()
        ->and(static fn (): Customer => $customers->get($id))->toThrow(CustomerNotFound::class)
        ->and(static fn () => $customers->saveProfile($id, new CustomerProfile))->toThrow(CustomerNotFound::class)
        ->and(static fn () => $customers->anonymise($id, $now))->toThrow(CustomerNotFound::class)
        ->and($customers->register(PhoneNumber::from('+5511912345678'), $now))->not->toEqual($id);
})->with('customer repositories');

it('keeps the anonymous row, with no personal data, for the ledger', function (): void {
    $customers = new EloquentCustomerRepository;
    $now = new DateTimeImmutable('2026-11-25 12:00:00');
    $id = $customers->register(PhoneNumber::from('+5511912345678'), $now);
    $customers->saveProfile($id, CustomerProfile::from('Maria', '1990-05-17', '52998224725', $now));

    $customers->anonymise($id, $now);

    expect((array) DB::table('customers')->where('id', $id->value)->first(['phone', 'name', 'birth_date', 'document', 'phone_verified_at', 'deleted_at']))
        ->toBe([
            'phone' => null,
            'name' => null,
            'birth_date' => null,
            'document' => null,
            'phone_verified_at' => null,
            'deleted_at' => '2026-11-25 12:00:00',
        ]);
});

it('refuses to read a row no form writes', function (): void {
    $record = new CustomerRecord;
    $record->forceFill(['phone' => '+5511912345678', 'document' => '123', 'phone_verified_at' => now()])->save();

    expect(static fn (): Customer => new EloquentCustomerRepository()->get(new CustomerId($record->id)))
        ->toThrow(UnexpectedValueException::class, "The customer {$record->id} holds a value no form writes.");
});
