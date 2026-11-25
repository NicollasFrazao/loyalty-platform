<?php

declare(strict_types=1);

namespace App\Application\Tenancy;

use App\Domain\Tenancy\StaffMember;
use App\Domain\Tenancy\StaffRepository;

/**
 * The staff of the acting staff member's own company (use case).
 */
final readonly class ListStaff
{
    /**
     * Takes the staff.
     */
    public function __construct(private StaffRepository $staff) {}

    /**
     * The company's staff, by name.
     *
     * @return list<StaffMember>
     */
    public function handle(ActingStaff $acting): array
    {
        return $this->staff->forCompany($acting->company->id);
    }
}
