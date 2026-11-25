<?php

declare(strict_types=1);

namespace Tests\Support\Tenancy;

use App\Domain\Tenancy\Company;
use App\Domain\Tenancy\CompanyId;
use App\Domain\Tenancy\CompanyNotFound;
use App\Domain\Tenancy\CompanyRepository;

/**
 * Keeps companies in memory, keyed by id.
 */
final class InMemoryCompanyRepository implements CompanyRepository
{
    /**
     * The companies, keyed by id.
     *
     * @var array<int, Company>
     */
    private array $companies = [];

    /**
     * Holds the companies the test starts with.
     */
    public function __construct(Company ...$companies)
    {
        foreach ($companies as $company) {
            $this->companies[$company->id->value] = $company;
        }
    }

    /**
     * The company with the id.
     *
     * @throws CompanyNotFound when there is none.
     */
    public function get(CompanyId $id): Company
    {
        return $this->companies[$id->value] ?? throw CompanyNotFound::withId($id);
    }

    /**
     * Replaces the company kept under its id.
     *
     * @throws CompanyNotFound when there is none.
     */
    public function save(Company $company): void
    {
        $this->get($company->id);
        $this->companies[$company->id->value] = $company;
    }
}
