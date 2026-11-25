<?php

declare(strict_types=1);

namespace App\Domain\Level;

use App\Domain\Customer\CustomerId;
use App\Domain\Tenancy\CompanyId;

/**
 * A customer at a company: the pair levels and progress belong to.
 */
final readonly class Standing
{
    /**
     * Pairs the customer with the company.
     */
    public function __construct(
        public CompanyId $company,
        public CustomerId $customer,
    ) {}
}
