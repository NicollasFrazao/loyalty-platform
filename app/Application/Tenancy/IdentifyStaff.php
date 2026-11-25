<?php

declare(strict_types=1);

namespace App\Application\Tenancy;

use App\Domain\Tenancy\CompanyNotFound;
use App\Domain\Tenancy\CompanyRepository;
use App\Domain\Tenancy\StaffMemberId;
use App\Domain\Tenancy\StaffMemberNotFound;
use App\Domain\Tenancy\StaffRepository;

/**
 * Turns the staff member id the session keeps into the acting staff member with their company
 * (use case).
 */
final readonly class IdentifyStaff
{
    /**
     * Takes the staff and the companies.
     */
    public function __construct(
        private StaffRepository $staff,
        private CompanyRepository $companies,
    ) {}

    /**
     * The acting staff member.
     *
     * @throws StaffMemberNotFound when no staff member has the id.
     * @throws CompanyNotFound when their company is gone.
     */
    public function handle(StaffMemberId $id): ActingStaff
    {
        $member = $this->staff->signedIn($id);

        return new ActingStaff($member, $this->companies->get($member->company));
    }
}
