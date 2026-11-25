<?php

declare(strict_types=1);

namespace App\Application\CheckIn;

use App\Application\Tenancy\ActingStaff;
use App\Domain\CheckIn\CheckIn;
use App\Domain\CheckIn\CheckInConfirmed;
use App\Domain\CheckIn\CheckInDecided;
use App\Domain\CheckIn\CheckInId;
use App\Domain\CheckIn\CheckInNotFound;
use App\Domain\CheckIn\CheckInRepository;
use App\Domain\CheckIn\CheckInStatus;
use App\Domain\Shared\EventPublisher;
use App\Domain\Tenancy\CompanySuspended;
use App\Domain\Tenancy\Module;
use App\Domain\Tenancy\ModuleDisabled;

/**
 * The check-ins of the acting staff member's company waiting for confirmation: any staff member
 * confirms or rejects them (use case).
 */
final readonly class ReviewCheckIns
{
    /**
     * Takes the check-ins and the publisher.
     */
    public function __construct(
        private CheckInRepository $checkIns,
        private EventPublisher $events,
    ) {}

    /**
     * The pending check-ins, oldest first.
     *
     * @return list<CheckIn>
     *
     * @throws CompanySuspended when the company is suspended.
     * @throws ModuleDisabled when the company has not enabled check-ins.
     */
    public function pending(ActingStaff $acting): array
    {
        $acting->company->ensureServes(Module::CheckIn);

        return $this->checkIns->pending($acting->company->id);
    }

    /**
     * Confirms the check-in, which then counts towards the customer's level.
     *
     * @throws CompanySuspended when the company is suspended.
     * @throws ModuleDisabled when the company has not enabled check-ins.
     * @throws CheckInNotFound when the company has no check-in with the id.
     * @throws CheckInDecided when it is not pending.
     */
    public function confirm(ActingStaff $acting, CheckInId $id): void
    {
        $checkIn = $this->decide($acting, $id, CheckInStatus::Confirmed);
        $this->events->publish(new CheckInConfirmed($acting->company->id, $checkIn->customer, $id));
    }

    /**
     * Rejects the check-in: it never counts, and does not hold the cooldown.
     *
     * @throws CompanySuspended when the company is suspended.
     * @throws ModuleDisabled when the company has not enabled check-ins.
     * @throws CheckInNotFound when the company has no check-in with the id.
     * @throws CheckInDecided when it is not pending.
     */
    public function reject(ActingStaff $acting, CheckInId $id): void
    {
        $this->decide($acting, $id, CheckInStatus::Rejected);
    }

    /**
     * Gives a pending check-in its status.
     *
     * @throws CompanySuspended when the company is suspended.
     * @throws ModuleDisabled when the company has not enabled check-ins.
     * @throws CheckInNotFound when the company has no check-in with the id.
     * @throws CheckInDecided when it is not pending.
     */
    private function decide(ActingStaff $acting, CheckInId $id, CheckInStatus $status): CheckIn
    {
        $acting->company->ensureServes(Module::CheckIn);
        $checkIn = $this->checkIns->get($acting->company->id, $id);

        if ($checkIn->status !== CheckInStatus::Pending) {
            throw CheckInDecided::already($checkIn);
        }

        $this->checkIns->decide($acting->company->id, $id, $status);

        return $checkIn;
    }
}
