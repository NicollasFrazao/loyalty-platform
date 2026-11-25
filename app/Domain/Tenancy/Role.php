<?php

declare(strict_types=1);

namespace App\Domain\Tenancy;

/**
 * What a staff member may do at their company: an operator serves customers, a manager also
 * configures the modules, an owner also manages the staff.
 */
enum Role: string
{
    case Owner = 'owner';
    case Manager = 'manager';
    case Operator = 'operator';

    /**
     * Tells whether the role may add and remove staff members.
     */
    public function canManageStaff(): bool
    {
        return $this === self::Owner;
    }

    /**
     * Tells whether the role may enable and disable modules.
     */
    public function canConfigure(): bool
    {
        return $this !== self::Operator;
    }
}
