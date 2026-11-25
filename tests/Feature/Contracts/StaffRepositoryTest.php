<?php

declare(strict_types=1);

use App\Domain\Tenancy\CompanyId;
use App\Domain\Tenancy\EmailAddress;
use App\Domain\Tenancy\EmailTaken;
use App\Domain\Tenancy\HashedPassword;
use App\Domain\Tenancy\NewStaffMember;
use App\Domain\Tenancy\Role;
use App\Domain\Tenancy\StaffMember;
use App\Domain\Tenancy\StaffMemberId;
use App\Domain\Tenancy\StaffMemberNotFound;
use App\Domain\Tenancy\StaffProfile;
use App\Domain\Tenancy\StaffRepository;
use App\Infrastructure\Persistence\Eloquent\CompanyRecord;
use App\Infrastructure\Persistence\EloquentStaffRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Tenancy\InMemoryStaffRepository;

uses(RefreshDatabase::class);

/*
 * The contract every StaffRepository keeps, given companies 1 and 2. The in-memory adapter is the
 * reference; the Eloquent adapter runs it on MySQL.
 */
dataset('staff repositories', [
    'in memory' => fn (): StaffRepository => new InMemoryStaffRepository,
    'eloquent' => function (): StaffRepository {
        CompanyRecord::factory()->create(['id' => 1]);
        CompanyRecord::factory()->create(['id' => 2]);

        return new EloquentStaffRepository;
    },
]);

/**
 * A new staff member of the company; the hash is a bcrypt one, as the user model keeps hashes it is
 * given and hashes anything else.
 */
function newMember(int $company, string $name, string $email, Role $role = Role::Operator): NewStaffMember
{
    return new NewStaffMember(
        new CompanyId($company),
        StaffProfile::from($name, $email),
        $role,
        new HashedPassword('$2y$04$RssHrF.3CaTxz7GfOb4NO.R9sBgAtKFqxHhe.GGNe1qQ3SPyfe9zu'),
    );
}

it('keeps each company\'s staff apart, by name', function (StaffRepository $staff): void {
    $bia = $staff->add(newMember(1, 'Bia', 'bia@a.example'));
    $ana = $staff->add(newMember(1, 'Ana', 'ana@a.example', Role::Owner));
    $caio = $staff->add(newMember(2, 'Caio', 'caio@b.example'));

    expect(array_map(static fn (StaffMember $member): string => $member->profile->name->value, $staff->forCompany(new CompanyId(1))))->toBe(['Ana', 'Bia'])
        ->and($staff->get(new CompanyId(1), $ana)->role)->toBe(Role::Owner)
        ->and($staff->signedIn($caio)->company)->toEqual(new CompanyId(2))
        ->and(static fn (): StaffMember => $staff->get(new CompanyId(2), $bia))->toThrow(StaffMemberNotFound::class)
        ->and(static fn () => $staff->remove(new CompanyId(2), $bia))->toThrow(StaffMemberNotFound::class);
})->with('staff repositories');

it('finds the credentials of an address, refuses a taken one, and removes a member', function (StaffRepository $staff): void {
    $bia = $staff->add(newMember(1, 'Bia', 'bia@a.example'));

    expect($staff->credentialsFor(EmailAddress::from('bia@a.example'))?->member->id)->toEqual($bia)
        ->and($staff->credentialsFor(EmailAddress::from('nobody@a.example')))->toBeNull()
        ->and(static fn (): StaffMemberId => $staff->add(newMember(2, 'Bia', 'bia@a.example')))->toThrow(EmailTaken::class);

    $staff->remove(new CompanyId(1), $bia);

    expect($staff->forCompany(new CompanyId(1)))->toBe([])
        ->and(static fn (): StaffMember => $staff->signedIn(new StaffMemberId(99)))->toThrow(StaffMemberNotFound::class);
})->with('staff repositories');
