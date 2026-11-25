<?php

declare(strict_types=1);

namespace App\Domain\CheckIn;

use App\Domain\Customer\CustomerId;
use DateTimeImmutable;

/**
 * A customer's recorded visit to a company. Only a confirmed check-in counts towards a level.
 */
final readonly class CheckIn
{
    /**
     * Describes the check-in.
     */
    public function __construct(
        public CheckInId $id,
        public CustomerId $customer,
        public DateTimeImmutable $at,
        public CheckInStatus $status,
    ) {}
}
