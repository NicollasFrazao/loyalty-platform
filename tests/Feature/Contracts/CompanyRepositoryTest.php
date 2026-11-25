<?php

declare(strict_types=1);

use App\Domain\Shared\Name;
use App\Domain\Tenancy\Company;
use App\Domain\Tenancy\CompanyId;
use App\Domain\Tenancy\CompanyNotFound;
use App\Domain\Tenancy\CompanyRepository;
use App\Domain\Tenancy\CompanyStatus;
use App\Domain\Tenancy\Module;
use App\Domain\Tenancy\Plan;
use App\Domain\Tenancy\Subscription;
use App\Infrastructure\Persistence\Eloquent\CompanyRecord;
use App\Infrastructure\Persistence\EloquentCompanyRepository;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Tenancy\InMemoryCompanyRepository;

uses(RefreshDatabase::class);

/**
 * Company 1 of the contract: Corner Bar, on the Growth plan, active, with no module.
 */
function cornerBar(): Company
{
    return new Company(new CompanyId(1), Name::from('Corner Bar'), new Subscription(Plan::Growth, CompanyStatus::Active));
}

/*
 * The contract every CompanyRepository keeps, given company 1. The in-memory adapter is the
 * reference; the Eloquent adapter runs it on MySQL.
 */
dataset('company repositories', [
    'in memory' => fn (): CompanyRepository => new InMemoryCompanyRepository(cornerBar()),
    'eloquent' => function (): CompanyRepository {
        CompanyRecord::factory()->create(['id' => 1, 'name' => 'Corner Bar', 'plan' => 'growth']);

        return new EloquentCompanyRepository(app(ConnectionInterface::class));
    },
]);

it('loads a company and saves its modules', function (CompanyRepository $companies): void {
    expect($companies->get(new CompanyId(1)))->toEqual(cornerBar());

    $companies->save(cornerBar()->enable(Module::Vouchers)->enable(Module::CheckIn));

    expect($companies->get(new CompanyId(1))->modules())->toBe([Module::Vouchers, Module::CheckIn]);

    $companies->save(cornerBar());

    expect($companies->get(new CompanyId(1))->modules())->toBe([]);
})->with('company repositories');

it('does not find or save a company that does not exist', function (CompanyRepository $companies): void {
    expect(static fn (): Company => $companies->get(new CompanyId(9)))->toThrow(CompanyNotFound::class)
        ->and(static fn () => $companies->save(new Company(new CompanyId(9), Name::from('Nowhere'), new Subscription(Plan::Starter, CompanyStatus::Active))))
        ->toThrow(CompanyNotFound::class);
})->with('company repositories');
