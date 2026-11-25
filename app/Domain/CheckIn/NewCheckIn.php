<?php

declare(strict_types=1);

namespace App\Domain\CheckIn;

use App\Domain\Customer\CustomerId;
use App\Domain\Tenancy\CompanyId;
use DateTimeImmutable;

/**
 * A check-in about to be recorded: who, where and when.
 */
final readonly class NewCheckIn
{
    /**
     * Describes the visit; the place is null when the company has no fence and the app sent none.
     */
    public function __construct(
        public CompanyId $company,
        public CustomerId $customer,
        public DateTimeImmutable $at,
        public ?GeoPoint $where,
    ) {}
}
