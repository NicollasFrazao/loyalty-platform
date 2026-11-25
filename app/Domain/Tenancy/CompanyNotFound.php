<?php

declare(strict_types=1);

namespace App\Domain\Tenancy;

use App\Domain\Shared\DomainError;

/**
 * No company has the id asked for.
 */
final class CompanyNotFound extends DomainError
{
    /**
     * The error for the id.
     */
    public static function withId(CompanyId $company): self
    {
        return new self("No company has the id {$company->value}.");
    }
}
