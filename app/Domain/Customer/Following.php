<?php

declare(strict_types=1);

namespace App\Domain\Customer;

use App\Domain\Tenancy\CompanyId;
use DateTimeImmutable;

/**
 * Which companies each customer follows (port; implemented in Infrastructure). Following a company
 * makes the customer one of its customers (`company_customers`).
 */
interface Following
{
    /**
     * Records that the customer follows the company; following again changes nothing.
     */
    public function follow(CustomerId $customer, CompanyId $company, DateTimeImmutable $at): void;

    /**
     * Stops the customer following the company; nothing happens when they did not.
     */
    public function unfollow(CustomerId $customer, CompanyId $company): void;

    /**
     * The companies the customer follows, in the order they followed them.
     *
     * @return list<CompanyId>
     */
    public function companiesOf(CustomerId $customer): array;

    /**
     * Stops the customer following every company, when the account is deleted.
     */
    public function forget(CustomerId $customer): void;
}
