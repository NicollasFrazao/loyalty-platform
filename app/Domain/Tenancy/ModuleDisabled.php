<?php

declare(strict_types=1);

namespace App\Domain\Tenancy;

use App\Domain\Shared\DomainError;

/**
 * Something that belongs to a module was asked of a company that has not enabled it.
 */
final class ModuleDisabled extends DomainError
{
    /**
     * The error for the module and the company.
     */
    public static function of(Module $module, CompanyId $company): self
    {
        return new self("Company {$company->value} has not enabled the {$module->value} module.");
    }
}
