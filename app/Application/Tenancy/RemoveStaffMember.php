<?php

declare(strict_types=1);

namespace App\Application\Tenancy;

use App\Domain\Tenancy\NotAllowed;
use App\Domain\Tenancy\StaffMemberId;
use App\Domain\Tenancy\StaffMemberNotFound;
use App\Domain\Tenancy\StaffRepository;

/**
 * Removes a staff member of the acting staff member's own company; only owners may, and nobody
 * removes themselves (use case).
 */
final readonly class RemoveStaffMember
{
    /**
     * Takes the staff.
     */
    public function __construct(private StaffRepository $staff) {}

    /**
     * Removes the staff member.
     *
     * @throws NotAllowed when the role cannot manage the staff, or the member is the acting one.
     * @throws StaffMemberNotFound when no member of the company has the id.
     */
    public function handle(ActingStaff $acting, StaffMemberId $id): void
    {
        $acting->ensureCanManageStaff();

        if ($id->value === $acting->member->id->value) {
            throw NotAllowed::removingYourself();
        }

        $this->staff->remove($acting->company->id, $id);
    }
}
