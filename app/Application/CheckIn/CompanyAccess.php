<?php

declare(strict_types=1);

namespace App\Application\CheckIn;

use App\Domain\Customer\CustomerId;
use App\Domain\Customer\Following;
use App\Domain\Shared\Clock;
use App\Domain\Tenancy\Company;
use App\Domain\Tenancy\CompanyId;
use App\Domain\Tenancy\CompanyNotFound;
use App\Domain\Tenancy\CompanyRepository;
use App\Domain\Tenancy\CompanySuspended;
use App\Domain\Tenancy\Module;
use App\Domain\Tenancy\ModuleDisabled;

/**
 * Lets a customer into one of a company's modules: the company must serve it, and using it follows
 * the company.
 */
final readonly class CompanyAccess
{
    /**
     * Takes the companies, the following and the clock.
     */
    public function __construct(
        private CompanyRepository $companies,
        private Following $following,
        private Clock $clock,
    ) {}

    /**
     * The company, once the customer follows it.
     *
     * @throws CompanyNotFound when no company has the id.
     * @throws CompanySuspended when the company is suspended.
     * @throws ModuleDisabled when the company has not enabled the module.
     */
    public function admit(CustomerId $customer, CompanyId $id, Module $module): Company
    {
        $company = $this->companies->get($id);
        $company->ensureServes($module);
        $this->following->follow($customer, $id, $this->clock->now());

        return $company;
    }
}
