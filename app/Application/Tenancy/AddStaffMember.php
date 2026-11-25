<?php

declare(strict_types=1);

namespace App\Application\Tenancy;

use App\Domain\Tenancy\EmailTaken;
use App\Domain\Tenancy\InvalidStaffData;
use App\Domain\Tenancy\NewStaffMember;
use App\Domain\Tenancy\NotAllowed;
use App\Domain\Tenancy\PasswordHasher;
use App\Domain\Tenancy\PlainPassword;
use App\Domain\Tenancy\StaffLimitReached;
use App\Domain\Tenancy\StaffMemberId;
use App\Domain\Tenancy\StaffProfile;
use App\Domain\Tenancy\StaffRepository;

/**
 * Adds a staff member to the acting staff member's own company, within the plan's limit; only
 * owners may (use case).
 */
final readonly class AddStaffMember
{
    /**
     * Takes the staff and the password hasher.
     */
    public function __construct(
        private StaffRepository $staff,
        private PasswordHasher $hasher,
    ) {}

    /**
     * Adds the staff member.
     *
     * @throws NotAllowed when the role cannot manage the staff.
     * @throws StaffLimitReached when the plan allows no more staff members.
     * @throws InvalidStaffData when the name, the email or the password is refused.
     * @throws EmailTaken when a staff member of any company signs in with the address.
     */
    public function handle(ActingStaff $acting, StaffInput $input): StaffMemberId
    {
        $acting->ensureCanManageStaff();
        $plan = $acting->company->plan;

        if (count($this->staff->forCompany($acting->company->id)) >= $plan->staffLimit()) {
            throw StaffLimitReached::of($plan);
        }

        $profile = StaffProfile::from($input->name, $input->email);
        $password = $this->hasher->hash(PlainPassword::from($input->password));

        return $this->staff->add(new NewStaffMember($acting->company->id, $profile, $input->role, $password));
    }
}
