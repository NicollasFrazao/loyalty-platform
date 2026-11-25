<?php

declare(strict_types=1);

namespace App\Application\Tenancy;

use App\Domain\Tenancy\CompanyNotFound;
use App\Domain\Tenancy\CompanyRepository;
use App\Domain\Tenancy\Module;
use App\Domain\Tenancy\ModuleNotInPlan;
use App\Domain\Tenancy\NotAllowed;

/**
 * Turns the modules of the acting staff member's own company on and off (use case); only owners and
 * managers may.
 */
final readonly class ConfigureModules
{
    /**
     * Takes the companies.
     */
    public function __construct(private CompanyRepository $companies) {}

    /**
     * Enables the module.
     *
     * @throws NotAllowed when the role cannot configure the company.
     * @throws ModuleNotInPlan when the plan does not include the module.
     * @throws CompanyNotFound when the company is gone.
     */
    public function enable(ActingStaff $acting, Module $module): void
    {
        $acting->ensureCanConfigure();
        $this->companies->save($acting->company->enable($module));
    }

    /**
     * Disables the module.
     *
     * @throws NotAllowed when the role cannot configure the company.
     * @throws CompanyNotFound when the company is gone.
     */
    public function disable(ActingStaff $acting, Module $module): void
    {
        $acting->ensureCanConfigure();
        $this->companies->save($acting->company->disable($module));
    }
}
