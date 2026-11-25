<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Shared\InvalidName;
use App\Domain\Shared\Name;
use App\Domain\Tenancy\CompanyId;
use App\Domain\Tenancy\EmailAddress;
use App\Domain\Tenancy\InvalidStaffData;
use App\Domain\Tenancy\Role;
use App\Domain\Tenancy\StaffMember;
use App\Domain\Tenancy\StaffMemberId;
use App\Domain\Tenancy\StaffProfile;
use App\Infrastructure\Persistence\Eloquent\User;
use UnexpectedValueException;
use ValueError;

/**
 * Maps a row of `users` to the domain's staff member, for {@see EloquentStaffRepository}.
 */
final class StaffRows
{
    /**
     * The staff member a row stands for.
     *
     * @throws UnexpectedValueException when the row holds a value no form writes.
     */
    public static function toMember(User $user): StaffMember
    {
        try {
            return new StaffMember(
                new StaffMemberId($user->id),
                new CompanyId($user->company_id),
                new StaffProfile(Name::from($user->name), EmailAddress::from($user->email)),
                Role::from($user->role),
            );
        } catch (InvalidName|InvalidStaffData|ValueError $invalid) {
            throw new UnexpectedValueException("The staff member {$user->id} holds a value no form writes.", 0, $invalid);
        }
    }
}
