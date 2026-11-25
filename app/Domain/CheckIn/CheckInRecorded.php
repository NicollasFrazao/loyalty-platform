<?php

declare(strict_types=1);

namespace App\Domain\CheckIn;

use App\Domain\Shared\DomainEvent;
use App\Domain\Tenancy\CompanyId;

/**
 * A customer checked in at a company; the check-in is confirmed, or pending a staff member.
 */
final readonly class CheckInRecorded implements DomainEvent
{
    /**
     * Describes the event.
     */
    public function __construct(
        public CompanyId $company,
        public CheckIn $checkIn,
    ) {}
}
