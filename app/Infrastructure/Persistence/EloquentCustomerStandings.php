<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\CheckIn\CheckInStatus;
use App\Domain\Level\CustomerStandings;
use App\Domain\Level\Level;
use App\Domain\Level\Progress;
use App\Domain\Level\Standing;
use App\Infrastructure\Persistence\Eloquent\CheckInRecord;
use App\Infrastructure\Persistence\Eloquent\CompanyCustomerRecord;
use Illuminate\Database\Eloquent\Builder;

/**
 * Progress read from `check_ins` and `company_customers.spent_cents`, and the level kept in
 * `company_customers.level`.
 */
final readonly class EloquentCustomerStandings implements CustomerStandings
{
    /**
     * The customer's confirmed check-ins and spend at the company.
     */
    public function progress(Standing $standing): Progress
    {
        $checkIns = CheckInRecord::query()
            ->where('company_id', $standing->company->value)
            ->where('customer_id', $standing->customer->value)
            ->where('status', CheckInStatus::Confirmed->value)
            ->count();

        return new Progress($checkIns, $this->row($standing)->first()->spent_cents ?? 0);
    }

    /**
     * The level recorded for the customer, if any.
     */
    public function level(Standing $standing): ?string
    {
        return $this->row($standing)->first()?->level;
    }

    /**
     * Writes the level on the customer's row of the company.
     */
    public function recordLevel(Standing $standing, Level $level): void
    {
        $this->row($standing)->update(['level' => $level->name->value]);
    }

    /**
     * The customer's row of the company.
     *
     * @return Builder<CompanyCustomerRecord>
     */
    private function row(Standing $standing): Builder
    {
        return CompanyCustomerRecord::query()
            ->where('company_id', $standing->company->value)
            ->where('customer_id', $standing->customer->value);
    }
}
