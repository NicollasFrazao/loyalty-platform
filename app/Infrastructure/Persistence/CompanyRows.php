<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Shared\InvalidName;
use App\Domain\Shared\Name;
use App\Domain\Tenancy\Company;
use App\Domain\Tenancy\CompanyId;
use App\Domain\Tenancy\CompanyStatus;
use App\Domain\Tenancy\Module;
use App\Domain\Tenancy\ModuleNotInPlan;
use App\Domain\Tenancy\Plan;
use App\Domain\Tenancy\Subscription;
use App\Infrastructure\Persistence\Eloquent\CompanyModuleRecord;
use App\Infrastructure\Persistence\Eloquent\CompanyRecord;
use UnexpectedValueException;
use ValueError;

/**
 * Maps a row of `companies` and its modules to the domain's company, for
 * {@see EloquentCompanyRepository}.
 */
final class CompanyRows
{
    /**
     * The company a row stands for.
     *
     * @throws UnexpectedValueException when the row holds a value no migration or form writes.
     */
    public static function toCompany(CompanyRecord $record): Company
    {
        try {
            return new Company(
                new CompanyId($record->id),
                Name::from($record->name),
                new Subscription(Plan::from($record->plan), CompanyStatus::from($record->status)),
                ...$record->modules->map(static fn (CompanyModuleRecord $module): Module => Module::from($module->module))->all(),
            );
        } catch (InvalidName|ModuleNotInPlan|ValueError $invalid) {
            throw new UnexpectedValueException("The company {$record->id} holds a value no form writes.", 0, $invalid);
        }
    }
}
