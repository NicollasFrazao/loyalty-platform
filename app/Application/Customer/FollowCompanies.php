<?php

declare(strict_types=1);

namespace App\Application\Customer;

use App\Domain\Customer\CustomerId;
use App\Domain\Customer\Following;
use App\Domain\Shared\Clock;
use App\Domain\Tenancy\Company;
use App\Domain\Tenancy\CompanyId;
use App\Domain\Tenancy\CompanyNotFound;
use App\Domain\Tenancy\CompanyRepository;
use App\Domain\Tenancy\CompanyStatus;
use App\Domain\Tenancy\CompanySuspended;

/**
 * The companies a customer follows: following one makes them its customer (use case).
 */
final readonly class FollowCompanies
{
    /**
     * Takes the following, the companies and the clock.
     */
    public function __construct(
        private Following $following,
        private CompanyRepository $companies,
        private Clock $clock,
    ) {}

    /**
     * The companies the customer follows.
     *
     * @return list<Company>
     *
     * @throws CompanyNotFound when a followed company is gone, which the foreign keys prevent.
     */
    public function list(CustomerId $customer): array
    {
        return array_map($this->companies->get(...), $this->following->companiesOf($customer));
    }

    /**
     * Follows an active company.
     *
     * @throws CompanyNotFound when no company has the id.
     * @throws CompanySuspended when the company is suspended.
     */
    public function follow(CustomerId $customer, CompanyId $company): void
    {
        if ($this->companies->get($company)->status === CompanyStatus::Suspended) {
            throw CompanySuspended::withId($company);
        }

        $this->following->follow($customer, $company, $this->clock->now());
    }

    /**
     * Stops following a company.
     */
    public function unfollow(CustomerId $customer, CompanyId $company): void
    {
        $this->following->unfollow($customer, $company);
    }
}
