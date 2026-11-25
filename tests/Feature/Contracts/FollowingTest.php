<?php

declare(strict_types=1);

use App\Domain\Customer\CustomerId;
use App\Domain\Customer\Following;
use App\Domain\Customer\PhoneNumber;
use App\Domain\Tenancy\CompanyId;
use App\Infrastructure\Persistence\Eloquent\CompanyRecord;
use App\Infrastructure\Persistence\EloquentCustomerRepository;
use App\Infrastructure\Persistence\EloquentFollowing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Customer\InMemoryFollowing;

uses(RefreshDatabase::class);

/*
 * The contract every Following keeps, given companies 1, 2 and 3 and customers 1 and 2. The in-memory
 * adapter is the reference; the Eloquent adapter runs it on MySQL.
 */
dataset('followings', [
    'in memory' => fn (): array => [new InMemoryFollowing, new CustomerId(1), new CustomerId(2)],
    'eloquent' => function (): array {
        foreach ([1, 2, 3] as $company) {
            CompanyRecord::factory()->create(['id' => $company]);
        }

        $customers = new EloquentCustomerRepository;
        $now = new DateTimeImmutable;

        return [
            new EloquentFollowing,
            $customers->register(PhoneNumber::from('+5511912345678'), $now),
            $customers->register(PhoneNumber::from('+5521987654321'), $now),
        ];
    },
]);

it('lists the companies in the order they were followed, once each', function (Following $following, CustomerId $me, CustomerId $other): void {
    $now = new DateTimeImmutable('2026-11-25 12:00:00');
    $following->follow($me, new CompanyId(3), $now);
    $following->follow($me, new CompanyId(1), $now->modify('+1 minute'));
    $following->follow($me, new CompanyId(3), $now->modify('+2 minutes'));
    $following->follow($other, new CompanyId(2), $now);

    expect($following->companiesOf($me))->toEqual([new CompanyId(3), new CompanyId(1)])
        ->and($following->companiesOf($other))->toEqual([new CompanyId(2)]);
})->with('followings');

it('unfollows one company, or all of them', function (Following $following, CustomerId $me, CustomerId $other): void {
    $now = new DateTimeImmutable('2026-11-25 12:00:00');
    $following->follow($me, new CompanyId(1), $now);
    $following->follow($me, new CompanyId(2), $now->modify('+1 minute'));
    $following->follow($other, new CompanyId(1), $now);

    $following->unfollow($me, new CompanyId(1));
    $following->unfollow($me, new CompanyId(3));

    expect($following->companiesOf($me))->toEqual([new CompanyId(2)]);

    $following->forget($me);

    expect($following->companiesOf($me))->toBe([])
        ->and($following->companiesOf($other))->toEqual([new CompanyId(1)]);
})->with('followings');
