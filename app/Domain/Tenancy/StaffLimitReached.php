<?php

declare(strict_types=1);

namespace App\Domain\Tenancy;

use App\Domain\Shared\DomainError;

/**
 * The company has as many staff members as its plan allows.
 */
final class StaffLimitReached extends DomainError
{
    /**
     * The error for the plan.
     */
    public static function of(Plan $plan): self
    {
        return new self("The {$plan->value} plan allows {$plan->staffLimit()} staff members.");
    }
}
