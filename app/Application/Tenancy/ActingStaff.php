<?php

declare(strict_types=1);

namespace App\Application\Tenancy;

use App\Domain\Tenancy\Company;
use App\Domain\Tenancy\NotAllowed;
use App\Domain\Tenancy\StaffMember;

/**
 * The signed-in staff member with their company: the scope every back-office use case runs in. It
 * is built from the session only, so a use case cannot be pointed at another company.
 */
final readonly class ActingStaff
{
    /**
     * Pairs the member with their company.
     */
    public function __construct(
        public StaffMember $member,
        public Company $company,
    ) {}

    /**
     * Passes when the role may add and remove staff members.
     *
     * @throws NotAllowed when it may not.
     */
    public function ensureCanManageStaff(): void
    {
        if (! $this->member->role->canManageStaff()) {
            throw NotAllowed::forRole($this->member->role, 'manage the staff');
        }
    }

    /**
     * Passes when the role may enable and disable modules.
     *
     * @throws NotAllowed when it may not.
     */
    public function ensureCanConfigure(): void
    {
        if (! $this->member->role->canConfigure()) {
            throw NotAllowed::forRole($this->member->role, 'configure the modules');
        }
    }
}
