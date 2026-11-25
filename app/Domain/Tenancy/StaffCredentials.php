<?php

declare(strict_types=1);

namespace App\Domain\Tenancy;

/**
 * A staff member with the hash of their password, for signing in.
 */
final readonly class StaffCredentials
{
    /**
     * Pairs the member with the hash.
     */
    public function __construct(
        public StaffMember $member,
        public HashedPassword $password,
    ) {}
}
