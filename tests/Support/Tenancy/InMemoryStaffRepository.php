<?php

declare(strict_types=1);

namespace Tests\Support\Tenancy;

use App\Domain\Tenancy\CompanyId;
use App\Domain\Tenancy\EmailAddress;
use App\Domain\Tenancy\EmailTaken;
use App\Domain\Tenancy\NewStaffMember;
use App\Domain\Tenancy\StaffCredentials;
use App\Domain\Tenancy\StaffMember;
use App\Domain\Tenancy\StaffMemberId;
use App\Domain\Tenancy\StaffMemberNotFound;
use App\Domain\Tenancy\StaffRepository;

/**
 * Keeps staff members and their password hashes in memory, numbered from 1.
 */
final class InMemoryStaffRepository implements StaffRepository
{
    /**
     * The staff with their hashes, keyed by id.
     *
     * @var array<int, StaffCredentials>
     */
    private array $staff = [];

    /**
     * The company's staff, by name.
     *
     * @return list<StaffMember>
     */
    public function forCompany(CompanyId $company): array
    {
        $members = [];

        foreach ($this->staff as $credentials) {
            if (! $credentials->member->worksFor($company)) {
                continue;
            }

            $members[] = $credentials->member;
        }

        usort($members, static fn (StaffMember $a, StaffMember $b): int => strcmp($a->profile->name->value, $b->profile->name->value));

        return $members;
    }

    /**
     * The company's staff member with the id.
     *
     * @throws StaffMemberNotFound when no member of the company has it.
     */
    public function get(CompanyId $company, StaffMemberId $id): StaffMember
    {
        $member = $this->signedIn($id);

        return $member->worksFor($company) ? $member : throw StaffMemberNotFound::withId($id);
    }

    /**
     * Adds the staff member under the next id.
     *
     * @throws EmailTaken when someone signs in with the address.
     */
    public function add(NewStaffMember $member): StaffMemberId
    {
        if ($this->credentialsFor($member->profile->email) instanceof StaffCredentials) {
            throw EmailTaken::create();
        }

        $id = new StaffMemberId(count($this->staff) + 1);
        $this->staff[$id->value] = new StaffCredentials(new StaffMember($id, $member->company, $member->profile, $member->role), $member->password);

        return $id;
    }

    /**
     * Removes the company's staff member.
     *
     * @throws StaffMemberNotFound when no member of the company has the id.
     */
    public function remove(CompanyId $company, StaffMemberId $id): void
    {
        $this->get($company, $id);
        unset($this->staff[$id->value]);
    }

    /**
     * The staff member who signs in with the address.
     */
    public function credentialsFor(EmailAddress $email): ?StaffCredentials
    {
        foreach ($this->staff as $credentials) {
            if ($credentials->member->profile->email->value === $email->value) {
                return $credentials;
            }
        }

        return null;
    }

    /**
     * The staff member with the id.
     *
     * @throws StaffMemberNotFound when there is none.
     */
    public function signedIn(StaffMemberId $id): StaffMember
    {
        return ($this->staff[$id->value] ?? throw StaffMemberNotFound::withId($id))->member;
    }
}
