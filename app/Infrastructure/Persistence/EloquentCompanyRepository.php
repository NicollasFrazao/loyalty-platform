<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Tenancy\Company;
use App\Domain\Tenancy\CompanyId;
use App\Domain\Tenancy\CompanyNotFound;
use App\Domain\Tenancy\CompanyRepository;
use App\Domain\Tenancy\Module;
use App\Infrastructure\Persistence\Eloquent\CompanyModuleRecord;
use App\Infrastructure\Persistence\Eloquent\CompanyRecord;
use Illuminate\Database\ConnectionInterface;
use UnexpectedValueException;

/**
 * Companies kept in `companies`, with their modules in `company_modules`.
 */
final readonly class EloquentCompanyRepository implements CompanyRepository
{
    /**
     * Takes the connection whose transaction makes a save all or nothing.
     */
    public function __construct(private ConnectionInterface $connection) {}

    /**
     * Loads the company with its modules.
     *
     * @throws CompanyNotFound when no row has the id.
     * @throws UnexpectedValueException when the row holds a value no form writes.
     */
    public function get(CompanyId $id): Company
    {
        return CompanyRows::toCompany(
            CompanyRecord::query()->with('modules')->find($id->value) ?? throw CompanyNotFound::withId($id),
        );
    }

    /**
     * Writes the status and replaces the modules, in one transaction.
     *
     * @throws CompanyNotFound when no row has the company's id.
     */
    public function save(Company $company): void
    {
        $this->connection->transaction(static function () use ($company): void {
            $record = CompanyRecord::query()->lockForUpdate()->find($company->id->value) ?? throw CompanyNotFound::withId($company->id);
            $record->update(['status' => $company->status->value]);
            CompanyModuleRecord::query()->where('company_id', $record->id)->delete();
            CompanyModuleRecord::query()->insert(array_map(
                static fn (Module $module): array => ['company_id' => $record->id, 'module' => $module->value],
                $company->modules(),
            ));
        });
    }
}
