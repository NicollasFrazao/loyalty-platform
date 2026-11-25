<?php

declare(strict_types=1);

namespace App\Domain\Tenancy;

/**
 * A person who works for one company and uses its back office, with a role there.
 */
final readonly class StaffMember
{
    /**
     * Describes the staff member.
     */
    public function __construct(
        public StaffMemberId $id,
        public CompanyId $company,
        public StaffProfile $profile,
        public Role $role,
    ) {}

    /**
     * Tells whether the staff member works for the company.
     */
    public function worksFor(CompanyId $company): bool
    {
        return $this->company->value === $company->value;
    }
}
