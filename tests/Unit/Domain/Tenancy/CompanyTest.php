<?php

declare(strict_types=1);

use App\Domain\Shared\Name;
use App\Domain\Tenancy\Company;
use App\Domain\Tenancy\CompanyId;
use App\Domain\Tenancy\CompanyStatus;
use App\Domain\Tenancy\CompanySuspended;
use App\Domain\Tenancy\Module;
use App\Domain\Tenancy\ModuleDisabled;
use App\Domain\Tenancy\ModuleNotInPlan;
use App\Domain\Tenancy\Plan;
use App\Domain\Tenancy\Role;
use App\Domain\Tenancy\StaffMember;
use App\Domain\Tenancy\StaffMemberId;

/**
 * A company on the plan given, active unless said otherwise, with the modules given.
 */
function company(Plan $plan, CompanyStatus $status = CompanyStatus::Active, Module ...$modules): Company
{
    return new Company(new CompanyId(1), Name::from('Corner Bar'), $plan, $status, ...$modules);
}

it('lets a plan allow its modules and its staff', function (Plan $plan, array $allowed, int $staff): void {
    expect(array_values(array_filter(Module::cases(), $plan->allows(...))))->toBe($allowed)
        ->and($plan->staffLimit())->toBe($staff);
})->with([
    'starter' => [Plan::Starter, [Module::CheckIn, Module::Cashback], 3],
    'growth' => [Plan::Growth, [Module::CheckIn, Module::Cashback, Module::Vouchers, Module::Notifications], 10],
    'complete' => [Plan::Complete, Module::cases(), 50],
]);

it('enables and disables the modules its plan allows', function (): void {
    $company = company(Plan::Growth)->enable(Module::CheckIn)->enable(Module::Vouchers)->enable(Module::CheckIn);

    expect($company->modules())->toBe([Module::CheckIn, Module::Vouchers])
        ->and($company->disable(Module::CheckIn)->modules())->toBe([Module::Vouchers])
        ->and($company->hasEnabled(Module::Cashback))->toBeFalse();
});

it('refuses a module the plan does not include', function (): void {
    company(Plan::Starter)->enable(Module::Memberships);
})->throws(ModuleNotInPlan::class, 'The starter plan does not include the memberships module.');

it('serves a module only while active and enabled', function (): void {
    $active = company(Plan::Starter, CompanyStatus::Active, Module::CheckIn);
    $suspended = company(Plan::Starter, CompanyStatus::Suspended, Module::CheckIn);

    $active->ensureServes(Module::CheckIn);

    expect(static fn () => $active->ensureServes(Module::Cashback))->toThrow(ModuleDisabled::class)
        ->and(static fn () => $suspended->ensureServes(Module::CheckIn))->toThrow(CompanySuspended::class);
});

it('gives each role what it may do, and ties a staff member to one company', function (): void {
    $operator = new StaffMember(new StaffMemberId(7), new CompanyId(1), Name::from('Ana'), Role::Operator);

    expect([Role::Owner->canManageStaff(), Role::Manager->canManageStaff(), Role::Operator->canManageStaff()])->toBe([true, false, false])
        ->and([Role::Owner->canConfigure(), Role::Manager->canConfigure(), Role::Operator->canConfigure()])->toBe([true, true, false])
        ->and($operator->worksFor(new CompanyId(1)))->toBeTrue()
        ->and($operator->worksFor(new CompanyId(2)))->toBeFalse();
});
