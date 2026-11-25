<?php

declare(strict_types=1);

namespace App\Domain\Tenancy;

use App\Domain\Shared\DomainError;

/**
 * A suspended company was asked to do business.
 */
final class CompanySuspended extends DomainError
{
    /**
     * The error for the company.
     */
    public static function withId(CompanyId $company): self
    {
        return new self("Company {$company->value} is suspended.");
    }
}
