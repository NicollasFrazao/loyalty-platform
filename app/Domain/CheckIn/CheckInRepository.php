<?php

declare(strict_types=1);

namespace App\Domain\CheckIn;

use App\Domain\Customer\CustomerId;
use App\Domain\Tenancy\CompanyId;
use DateTimeImmutable;

/**
 * A company's check-in settings and its check-ins (port; implemented in Infrastructure). Every method
 * takes the company: another company's check-in is not found.
 */
interface CheckInRepository
{
    /**
     * The company's rules; the defaults when it configured none.
     */
    public function settings(CompanyId $company): CheckInSettings;

    /**
     * Replaces the company's rules.
     */
    public function saveSettings(CompanyId $company, CheckInSettings $settings): void;

    /**
     * Records the check-in with its first status.
     */
    public function record(NewCheckIn $checkIn, CheckInStatus $status): CheckIn;

    /**
     * When the customer last checked in at the company, the rejected check-ins aside.
     */
    public function lastAt(CompanyId $company, CustomerId $customer): ?DateTimeImmutable;

    /**
     * How many confirmed check-ins the customer has at the company.
     */
    public function countConfirmed(CompanyId $company, CustomerId $customer): int;

    /**
     * The company's check-ins waiting for a staff member, oldest first.
     *
     * @return list<CheckIn>
     */
    public function pending(CompanyId $company): array;

    /**
     * The company's check-in with the id.
     *
     * @throws CheckInNotFound when the company has none with it.
     */
    public function get(CompanyId $company, CheckInId $id): CheckIn;

    /**
     * Confirms or rejects the company's check-in.
     *
     * @throws CheckInNotFound when the company has none with the id.
     */
    public function decide(CompanyId $company, CheckInId $id, CheckInStatus $status): void;
}
