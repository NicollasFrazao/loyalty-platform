<?php

declare(strict_types=1);

namespace App\Domain\Tenancy;

use App\Domain\Shared\DomainError;

/**
 * The staff member does not exist, or works for another company: the two are the same answer, so a
 * company cannot learn who works elsewhere.
 */
final class StaffMemberNotFound extends DomainError
{
    /**
     * The error for the id.
     */
    public static function withId(StaffMemberId $id): self
    {
        return new self("No staff member of this company has the id {$id->value}.");
    }
}
