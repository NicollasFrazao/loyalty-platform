<?php

declare(strict_types=1);

namespace App\Application\CheckIn;

use App\Domain\CheckIn\CheckIn;
use App\Domain\CheckIn\CheckInConfirmed;
use App\Domain\CheckIn\CheckInRecorded;
use App\Domain\CheckIn\CheckInRefused;
use App\Domain\CheckIn\CheckInRepository;
use App\Domain\CheckIn\CheckInRules;
use App\Domain\CheckIn\CheckInStatus;
use App\Domain\CheckIn\GeoPoint;
use App\Domain\CheckIn\NewCheckIn;
use App\Domain\Customer\CustomerId;
use App\Domain\Shared\Clock;
use App\Domain\Shared\EventPublisher;
use App\Domain\Tenancy\CompanyId;
use App\Domain\Tenancy\CompanyNotFound;
use App\Domain\Tenancy\CompanySuspended;
use App\Domain\Tenancy\Module;
use App\Domain\Tenancy\ModuleDisabled;

/**
 * A customer checks in at a company from the app, within the company's rules (use case).
 */
final readonly class CheckInAtCompany
{
    /**
     * Takes the access to companies, the check-ins, the publisher and the clock.
     */
    public function __construct(
        private CompanyAccess $access,
        private CheckInRepository $checkIns,
        private EventPublisher $events,
        private Clock $clock,
    ) {}

    /**
     * Records the check-in, confirmed or pending as the company asks, and publishes it.
     *
     * @throws CompanyNotFound when no company has the id.
     * @throws CompanySuspended when the company is suspended.
     * @throws ModuleDisabled when the company has not enabled check-ins.
     * @throws CheckInRefused when the cooldown is not over or the place is outside the fence.
     */
    public function handle(CustomerId $customer, CompanyId $company, ?GeoPoint $where): CheckIn
    {
        $this->access->admit($customer, $company, Module::CheckIn);
        $settings = $this->checkIns->settings($company);
        $new = new NewCheckIn($company, $customer, $this->clock->now(), $where);
        CheckInRules::ensureAllowed($settings, $this->checkIns->lastAt($company, $customer), $new);

        $checkIn = $this->checkIns->record($new, $settings->initialStatus());
        $this->events->publish(new CheckInRecorded($company, $checkIn));

        if ($checkIn->status === CheckInStatus::Confirmed) {
            $this->events->publish(new CheckInConfirmed($company, $customer, $checkIn->id));
        }

        return $checkIn;
    }
}
