<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\CheckIn\CheckIn;
use App\Domain\CheckIn\CheckInId;
use App\Domain\CheckIn\CheckInNotFound;
use App\Domain\CheckIn\CheckInRepository;
use App\Domain\CheckIn\CheckInSettings;
use App\Domain\CheckIn\CheckInStatus;
use App\Domain\CheckIn\NewCheckIn;
use App\Domain\Customer\CustomerId;
use App\Domain\Tenancy\CompanyId;
use App\Infrastructure\Persistence\Eloquent\CheckInRecord;
use App\Infrastructure\Persistence\Eloquent\CheckInSettingsRecord;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Builder;

/**
 * Check-in rules in `check_in_settings` and check-ins in `check_ins`; every query is limited to the
 * company's rows.
 */
final readonly class EloquentCheckInRepository implements CheckInRepository
{
    /**
     * The company's rules, or the defaults.
     */
    public function settings(CompanyId $company): CheckInSettings
    {
        return CheckInRows::toSettings(CheckInSettingsRecord::query()->find($company->value));
    }

    /**
     * Writes the company's rules.
     */
    public function saveSettings(CompanyId $company, CheckInSettings $settings): void
    {
        CheckInSettingsRecord::query()->updateOrCreate(['company_id' => $company->value], CheckInRows::settingsColumns($settings));
    }

    /**
     * Writes the check-in.
     */
    public function record(NewCheckIn $checkIn, CheckInStatus $status): CheckIn
    {
        $record = new CheckInRecord;
        $record->forceFill([
            'company_id' => $checkIn->company->value,
            'customer_id' => $checkIn->customer->value,
            'status' => $status->value,
            'latitude' => $checkIn->where?->latitude,
            'longitude' => $checkIn->where?->longitude,
            'checked_in_at' => $checkIn->at,
        ])->save();

        return new CheckIn(new CheckInId($record->id), $checkIn->customer, $checkIn->at, $status);
    }

    /**
     * The moment of the customer's last check-in that was not rejected.
     */
    public function lastAt(CompanyId $company, CustomerId $customer): ?DateTimeImmutable
    {
        return $this->of($company)
            ->where('customer_id', $customer->value)
            ->where('status', '!=', CheckInStatus::Rejected->value)
            ->orderByDesc('checked_in_at')
            ->first()
            ?->checked_in_at;
    }

    /**
     * Counts the customer's confirmed check-ins.
     */
    public function countConfirmed(CompanyId $company, CustomerId $customer): int
    {
        return $this->of($company)->where('customer_id', $customer->value)->where('status', CheckInStatus::Confirmed->value)->count();
    }

    /**
     * The company's pending check-ins, oldest first.
     *
     * @return list<CheckIn>
     */
    public function pending(CompanyId $company): array
    {
        return array_values($this->of($company)
            ->where('status', CheckInStatus::Pending->value)
            ->orderBy('checked_in_at')
            ->orderBy('id')
            ->get()
            ->map(CheckInRows::toCheckIn(...))
            ->all());
    }

    /**
     * The company's check-in with the id.
     *
     * @throws CheckInNotFound when the company has none with it.
     */
    public function get(CompanyId $company, CheckInId $id): CheckIn
    {
        return CheckInRows::toCheckIn($this->record($company, $id));
    }

    /**
     * Writes the check-in's status.
     *
     * @throws CheckInNotFound when the company has none with the id.
     */
    public function decide(CompanyId $company, CheckInId $id, CheckInStatus $status): void
    {
        $this->record($company, $id)->forceFill(['status' => $status->value])->save();
    }

    /**
     * The company's row with the id.
     *
     * @throws CheckInNotFound when there is none.
     */
    private function record(CompanyId $company, CheckInId $id): CheckInRecord
    {
        return $this->of($company)->find($id->value) ?? throw CheckInNotFound::withId($id);
    }

    /**
     * The company's check-ins.
     *
     * @return Builder<CheckInRecord>
     */
    private function of(CompanyId $company): Builder
    {
        return CheckInRecord::query()->where('company_id', $company->value);
    }
}
