<?php

declare(strict_types=1);

namespace Tests\Support\Tenancy;

use App\Application\Tenancy\ActingStaff;
use App\Application\Tenancy\IdentifyStaff;
use App\Domain\Shared\Name;
use App\Domain\Tenancy\Company;
use App\Domain\Tenancy\CompanyId;
use App\Domain\Tenancy\CompanyStatus;
use App\Domain\Tenancy\HashedPassword;
use App\Domain\Tenancy\NewStaffMember;
use App\Domain\Tenancy\Plan;
use App\Domain\Tenancy\Role;
use App\Domain\Tenancy\StaffProfile;
use App\Domain\Tenancy\Subscription;

/**
 * Two companies in memory, 1 (Corner Bar, Starter) and 2 (Harbour Café, Complete), and a way to
 * add staff to them and act as one.
 */
final readonly class TenancyKit
{
    /**
     * The companies.
     */
    public InMemoryCompanyRepository $companies;

    /**
     * The staff.
     */
    public InMemoryStaffRepository $staff;

    /**
     * The hasher.
     */
    public PrefixHasher $hasher;

    /**
     * Starts with the two companies and no staff.
     */
    public function __construct()
    {
        $this->companies = new InMemoryCompanyRepository(
            new Company(new CompanyId(1), Name::from('Corner Bar'), new Subscription(Plan::Starter, CompanyStatus::Active)),
            new Company(new CompanyId(2), Name::from('Harbour Café'), new Subscription(Plan::Complete, CompanyStatus::Active)),
        );
        $this->staff = new InMemoryStaffRepository;
        $this->hasher = new PrefixHasher;
    }

    /**
     * Adds a staff member (password "correct horse") to the company and acts as them.
     */
    public function actAs(int $company, Role $role, string $email): ActingStaff
    {
        $id = $this->staff->add(new NewStaffMember(
            new CompanyId($company),
            StaffProfile::from(ucfirst($role->value).' '.$email, $email),
            $role,
            new HashedPassword('hashed:correct horse'),
        ));

        return new IdentifyStaff($this->staff, $this->companies)->handle($id);
    }
}
