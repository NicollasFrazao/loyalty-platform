<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Tenancy\CompanyId;
use App\Domain\Tenancy\EmailAddress;
use App\Domain\Tenancy\EmailTaken;
use App\Domain\Tenancy\HashedPassword;
use App\Domain\Tenancy\NewStaffMember;
use App\Domain\Tenancy\StaffCredentials;
use App\Domain\Tenancy\StaffMember;
use App\Domain\Tenancy\StaffMemberId;
use App\Domain\Tenancy\StaffMemberNotFound;
use App\Domain\Tenancy\StaffRepository;
use App\Infrastructure\Persistence\Eloquent\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;

/**
 * Staff members kept in the framework's `users` table, each with their company and role; every
 * query of a company's staff is limited to the company's rows.
 */
final readonly class EloquentStaffRepository implements StaffRepository
{
    /**
     * The company's staff, by name.
     *
     * @return list<StaffMember>
     */
    public function forCompany(CompanyId $company): array
    {
        return array_values($this->of($company)->orderBy('name')->orderBy('id')->get()->map(StaffRows::toMember(...))->all());
    }

    /**
     * The company's staff member with the id.
     *
     * @throws StaffMemberNotFound when no member of the company has it.
     */
    public function get(CompanyId $company, StaffMemberId $id): StaffMember
    {
        return StaffRows::toMember($this->of($company)->find($id->value) ?? throw StaffMemberNotFound::withId($id));
    }

    /**
     * Writes the staff member's row.
     *
     * @throws EmailTaken when the unique index finds the address taken.
     */
    public function add(NewStaffMember $member): StaffMemberId
    {
        $user = new User;
        $user->forceFill([
            'company_id' => $member->company->value,
            'name' => $member->profile->name->value,
            'email' => $member->profile->email->value,
            'password' => $member->password->value,
            'role' => $member->role->value,
        ]);

        try {
            $user->save();
        } catch (UniqueConstraintViolationException) {
            throw EmailTaken::create();
        }

        return new StaffMemberId($user->id);
    }

    /**
     * Deletes the company's staff member.
     *
     * @throws StaffMemberNotFound when no member of the company has the id.
     */
    public function remove(CompanyId $company, StaffMemberId $id): void
    {
        ($this->of($company)->find($id->value) ?? throw StaffMemberNotFound::withId($id))->delete();
    }

    /**
     * The staff member who signs in with the address, with their hash.
     */
    public function credentialsFor(EmailAddress $email): ?StaffCredentials
    {
        $user = User::query()->where('email', $email->value)->first();

        return $user === null ? null : new StaffCredentials(StaffRows::toMember($user), new HashedPassword($user->getAuthPassword()));
    }

    /**
     * The staff member with the id, whatever their company.
     *
     * @throws StaffMemberNotFound when no row has the id.
     */
    public function signedIn(StaffMemberId $id): StaffMember
    {
        return StaffRows::toMember(User::query()->find($id->value) ?? throw StaffMemberNotFound::withId($id));
    }

    /**
     * The rows of the company's staff.
     *
     * @return Builder<User>
     */
    private function of(CompanyId $company): Builder
    {
        return User::query()->where('company_id', $company->value);
    }
}
