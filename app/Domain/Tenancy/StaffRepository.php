<?php

declare(strict_types=1);

namespace App\Domain\Tenancy;

/**
 * The staff of each company (port; implemented in Infrastructure). Every method that reads or
 * changes a company's staff takes the company: a member of another company is not found.
 */
interface StaffRepository
{
    /**
     * The company's staff, by name.
     *
     * @return list<StaffMember>
     */
    public function forCompany(CompanyId $company): array;

    /**
     * One staff member of the company.
     *
     * @throws StaffMemberNotFound when no member of the company has the id.
     */
    public function get(CompanyId $company, StaffMemberId $id): StaffMember;

    /**
     * Adds a staff member to their company.
     *
     * @throws EmailTaken when a staff member of any company signs in with the address.
     */
    public function add(NewStaffMember $member): StaffMemberId;

    /**
     * Removes a staff member of the company.
     *
     * @throws StaffMemberNotFound when no member of the company has the id.
     */
    public function remove(CompanyId $company, StaffMemberId $id): void;

    /**
     * The staff member who signs in with the address, with their password's hash: the one lookup
     * that is not inside a company, since signing in is what tells which company.
     */
    public function credentialsFor(EmailAddress $email): ?StaffCredentials;

    /**
     * The signed-in staff member, from the id the session keeps.
     *
     * @throws StaffMemberNotFound when no staff member has the id.
     */
    public function signedIn(StaffMemberId $id): StaffMember;
}
