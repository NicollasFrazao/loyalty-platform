<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Entry point of `php artisan db:seed`. Empty on purpose: the demo data arrives with the phases that
 * model it (companies in L1, customers in L2).
 */
final class DatabaseSeeder extends Seeder
{
    /**
     * Seeds the application's database.
     */
    public function run(): void
    {
        // The seeders of each phase are called from here as they arrive.
    }
}
