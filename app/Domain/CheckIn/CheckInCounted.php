<?php

declare(strict_types=1);

namespace App\Domain\CheckIn;

use App\Domain\Customer\CustomerId;
use App\Domain\Shared\DomainEvent;
use App\Domain\Tenancy\CompanyId;

/**
 * A customer's check-in started to count at a company: recorded confirmed, or confirmed later by a
 * staff member. Levels are recalculated on it.
 */
interface CheckInCounted extends DomainEvent
{
    /**
     * The company.
     */
    public function company(): CompanyId;

    /**
     * The customer.
     */
    public function customer(): CustomerId;
}
