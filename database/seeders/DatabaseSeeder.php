<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\CompanyRecord;
use Illuminate\Database\Seeder;

/**
 * Seeds a fresh database with the demo data: two companies and their staff (L1). The app container
 * runs it on every start (`migrate --seed`), so it does nothing once a company exists.
 */
final class DatabaseSeeder extends Seeder
{
    /**
     * Runs the seeders, unless the database was seeded already.
     */
    public function run(): void
    {
        if (CompanyRecord::query()->exists()) {
            $this->command?->info('Companies exist already; nothing seeded.');

            return;
        }

        $this->call([DemoCompaniesSeeder::class]);
    }
}
