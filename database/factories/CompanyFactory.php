<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Tenancy\CompanyStatus;
use App\Domain\Tenancy\Plan;
use App\Infrastructure\Persistence\Eloquent\CompanyRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Builds active companies on the Complete plan, so every module may be enabled.
 *
 * @extends Factory<CompanyRecord>
 */
final class CompanyFactory extends Factory
{
    /**
     * The model the factory builds.
     *
     * @var class-string<CompanyRecord>
     */
    protected $model = CompanyRecord::class;

    /**
     * The attributes of a new company.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'plan' => Plan::Complete->value,
            'status' => CompanyStatus::Active->value,
        ];
    }
}
