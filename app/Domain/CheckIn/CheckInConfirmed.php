<?php

declare(strict_types=1);

namespace App\Domain\CheckIn;

use App\Domain\Customer\CustomerId;
use App\Domain\Tenancy\CompanyId;

/**
 * A customer's check-in counts at a company from now on: it was recorded confirmed, or a staff
 * member confirmed it.
 */
final readonly class CheckInConfirmed implements CheckInCounted
{
    /**
     * Describes the event.
     */
    public function __construct(
        private CompanyId $company,
        private CustomerId $customer,
        public CheckInId $checkIn,
    ) {}

    /**
     * The company.
     */
    public function company(): CompanyId
    {
        return $this->company;
    }

    /**
     * The customer.
     */
    public function customer(): CustomerId
    {
        return $this->customer;
    }
}
