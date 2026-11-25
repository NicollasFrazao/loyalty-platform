<?php

declare(strict_types=1);

use App\Application\Tenancy\AddStaffMember;
use App\Application\Tenancy\ConfigureModules;
use App\Application\Tenancy\ListStaff;
use App\Application\Tenancy\RemoveStaffMember;
use App\Application\Tenancy\SignIn;
use App\Application\Tenancy\StaffInput;
use App\Domain\Tenancy\EmailTaken;
use App\Domain\Tenancy\InvalidCredentials;
use App\Domain\Tenancy\InvalidStaffData;
use App\Domain\Tenancy\Module;
use App\Domain\Tenancy\ModuleNotInPlan;
use App\Domain\Tenancy\NotAllowed;
use App\Domain\Tenancy\Role;
use App\Domain\Tenancy\StaffField;
use App\Domain\Tenancy\StaffLimitReached;
use App\Domain\Tenancy\StaffMember;
use App\Domain\Tenancy\StaffMemberId;
use App\Domain\Tenancy\StaffMemberNotFound;
use Tests\Support\Tenancy\TenancyKit;

/**
 * The emails of the staff members.
 *
 * @param  list<StaffMember>  $members  The members to read.
 * @return list<string>
 */
function emails(array $members): array
{
    return array_map(static fn (StaffMember $member): string => $member->profile->email->value, $members);
}

it('signs a staff member in with the email in any case, and refuses the same way otherwise', function (): void {
    $kit = new TenancyKit;
    $owner = $kit->actAs(1, Role::Owner, 'owner@corner.example');
    $signIn = new SignIn($kit->staff, $kit->hasher);

    expect($signIn->handle(' OWNER@corner.example', 'correct horse'))->toEqual($owner->member->id)
        ->and(static fn (): StaffMemberId => $signIn->handle('owner@corner.example', 'wrong horse'))->toThrow(InvalidCredentials::class)
        ->and(static fn (): StaffMemberId => $signIn->handle('nobody@corner.example', 'correct horse'))->toThrow(InvalidCredentials::class)
        ->and(static fn (): StaffMemberId => $signIn->handle('not an email', 'correct horse'))->toThrow(InvalidCredentials::class)
        ->and($kit->hasher->spentChecks)->toBe(2);
});

it('lists, adds and removes only the acting company\'s staff', function (): void {
    $kit = new TenancyKit;
    $owner = $kit->actAs(1, Role::Owner, 'owner@corner.example');
    $other = $kit->actAs(2, Role::Owner, 'owner@harbour.example');
    $add = new AddStaffMember($kit->staff, $kit->hasher);

    $id = $add->handle($owner, new StaffInput('Bia', 'bia@corner.example', Role::Operator, 'correct horse'));

    expect(emails(new ListStaff($kit->staff)->handle($owner)))->toBe(['bia@corner.example', 'owner@corner.example'])
        ->and(emails(new ListStaff($kit->staff)->handle($other)))->toBe(['owner@harbour.example'])
        ->and(static fn () => new RemoveStaffMember($kit->staff)->handle($other, $id))->toThrow(StaffMemberNotFound::class);

    new RemoveStaffMember($kit->staff)->handle($owner, $id);

    expect(emails(new ListStaff($kit->staff)->handle($owner)))->toBe(['owner@corner.example']);
});

it('lets only owners manage the staff, and nobody remove themselves', function (): void {
    $kit = new TenancyKit;
    $owner = $kit->actAs(1, Role::Owner, 'owner@corner.example');
    $manager = $kit->actAs(1, Role::Manager, 'manager@corner.example');
    $input = new StaffInput('Bia', 'bia@corner.example', Role::Operator, 'correct horse');

    expect(static fn (): StaffMemberId => new AddStaffMember($kit->staff, $kit->hasher)->handle($manager, $input))->toThrow(NotAllowed::class)
        ->and(static fn () => new RemoveStaffMember($kit->staff)->handle($manager, $owner->member->id))->toThrow(NotAllowed::class)
        ->and(static fn () => new RemoveStaffMember($kit->staff)->handle($owner, $owner->member->id))
        ->toThrow(NotAllowed::class, 'A staff member cannot remove themselves.');
});

it('keeps the staff within the plan and refuses what cannot be a staff member', function (): void {
    $kit = new TenancyKit;
    $owner = $kit->actAs(1, Role::Owner, 'owner@corner.example');
    $add = new AddStaffMember($kit->staff, $kit->hasher);
    $add->handle($owner, new StaffInput('Bia', 'bia@corner.example', Role::Operator, 'correct horse'));

    expect(static fn (): StaffMemberId => $add->handle($owner, new StaffInput('Bia', 'BIA@corner.example', Role::Operator, 'correct horse')))
        ->toThrow(EmailTaken::class);

    $add->handle($owner, new StaffInput('Caio', 'caio@corner.example', Role::Operator, 'correct horse'));

    expect(static fn (): StaffMemberId => $add->handle($owner, new StaffInput('Dani', 'dani@corner.example', Role::Operator, 'correct horse')))
        ->toThrow(StaffLimitReached::class, 'The starter plan allows 3 staff members.');
});

it('names the refused field of a new staff member', function (string $name, string $email, string $password, StaffField $field): void {
    $kit = new TenancyKit;
    $owner = $kit->actAs(2, Role::Owner, 'owner@harbour.example');

    try {
        new AddStaffMember($kit->staff, $kit->hasher)->handle($owner, new StaffInput($name, $email, Role::Manager, $password));
    } catch (InvalidStaffData $error) {
        expect($error->field)->toBe($field);

        return;
    }

    throw new LogicException('The staff member was accepted.');
})->with([
    'blank name' => [' ', 'bia@harbour.example', 'correct horse', StaffField::Name],
    'bad email' => ['Bia', 'bia', 'correct horse', StaffField::Email],
    'short password' => ['Bia', 'bia@harbour.example', 'short', StaffField::Password],
]);

it('lets owners and managers configure the modules of their own company, within the plan', function (): void {
    $kit = new TenancyKit;
    $manager = $kit->actAs(1, Role::Manager, 'manager@corner.example');
    $operator = $kit->actAs(1, Role::Operator, 'operator@corner.example');
    $configure = new ConfigureModules($kit->companies);

    $configure->enable($manager, Module::CheckIn);

    expect($kit->companies->get($manager->company->id)->modules())->toBe([Module::CheckIn])
        ->and($kit->companies->get($kit->actAs(2, Role::Owner, 'owner@harbour.example')->company->id)->modules())->toBe([])
        ->and(static fn () => $configure->enable($manager, Module::Memberships))->toThrow(ModuleNotInPlan::class)
        ->and(static fn () => $configure->enable($operator, Module::Cashback))->toThrow(NotAllowed::class);

    $configure->disable($manager, Module::CheckIn);

    expect($kit->companies->get($manager->company->id)->modules())->toBe([]);
});
