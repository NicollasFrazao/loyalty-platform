<?php

declare(strict_types=1);

namespace App\Domain\Tenancy;

/**
 * Where companies are kept (port; implemented in Infrastructure).
 */
interface CompanyRepository
{
    /**
     * Loads a company with its modules.
     *
     * @throws CompanyNotFound when no company has the id.
     */
    public function get(CompanyId $id): Company;

    /**
     * Stores the company's status and modules as they are now.
     *
     * @throws CompanyNotFound when no company has its id.
     */
    public function save(Company $company): void;
}
