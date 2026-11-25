<?php

declare(strict_types=1);

namespace App\Domain\Tenancy;

/**
 * A staff member about to be added to a company, with the hash of their password.
 */
final readonly class NewStaffMember
{
    /**
     * Describes the staff member.
     */
    public function __construct(
        public CompanyId $company,
        public StaffProfile $profile,
        public Role $role,
        public HashedPassword $password,
    ) {}
}
