<?php

declare(strict_types=1);

namespace App\Domain\Tenancy;

use App\Domain\Shared\DomainError;

/**
 * A staff member asked for something their role does not allow, or that would leave the company
 * without an owner.
 */
final class NotAllowed extends DomainError
{
    /**
     * The role cannot do the action.
     */
    public static function forRole(Role $role, string $action): self
    {
        return new self("A staff member with the {$role->value} role cannot {$action}.");
    }

    /**
     * Removing yourself would leave the company to the others.
     */
    public static function removingYourself(): self
    {
        return new self('A staff member cannot remove themselves.');
    }
}
