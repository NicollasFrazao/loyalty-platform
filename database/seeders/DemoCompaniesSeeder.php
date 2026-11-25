<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Tenancy\Module;
use App\Domain\Tenancy\Plan;
use App\Domain\Tenancy\Role;
use App\Infrastructure\Persistence\Eloquent\CompanyModuleRecord;
use App\Infrastructure\Persistence\Eloquent\CompanyRecord;
use App\Infrastructure\Persistence\Eloquent\User;
use Illuminate\Database\Seeder;

/**
 * Seeds two companies, each with an owner, a manager and an operator who sign in with the password
 * `password`: `owner@corner-bar.example` and so on, and `owner@harbour-cafe.example` and so on.
 */
final class DemoCompaniesSeeder extends Seeder
{
    /**
     * The companies: name, address domain, plan and the modules enabled.
     */
    public const array COMPANIES = [
        ['Corner Bar', 'corner-bar.example', Plan::Growth, [Module::CheckIn, Module::Cashback]],
        ['Harbour Café', 'harbour-cafe.example', Plan::Complete, [Module::CheckIn, Module::Cashback, Module::Vouchers, Module::Memberships]],
    ];

    /**
     * Creates the companies, their modules and their staff.
     */
    public function run(): void
    {
        foreach (self::COMPANIES as [$name, $domain, $plan, $modules]) {
            $company = CompanyRecord::factory()->create(['name' => $name, 'plan' => $plan->value]);

            foreach ($modules as $module) {
                CompanyModuleRecord::query()->create(['company_id' => $company->id, 'module' => $module->value]);
            }

            foreach (Role::cases() as $role) {
                User::factory()->withRole($role)->create([
                    'company_id' => $company->id,
                    'name' => ucfirst($role->value).' of '.$name,
                    'email' => "{$role->value}@{$domain}",
                ]);
            }
        }
    }
}
