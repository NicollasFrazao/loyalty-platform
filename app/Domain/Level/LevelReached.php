<?php

declare(strict_types=1);

namespace App\Domain\Level;

use App\Domain\Customer\CustomerId;
use App\Domain\Shared\DomainEvent;
use App\Domain\Tenancy\CompanyId;

/**
 * A customer's level at a company changed: the cashback of their next purchases is the new
 * level's. Usually a climb; a lower level when the company raised its thresholds.
 */
final readonly class LevelReached implements DomainEvent
{
    /**
     * Describes the event.
     *
     * @param  string|null  $previous  The name of the level before, or null on the first one.
     */
    public function __construct(
        public CompanyId $company,
        public CustomerId $customer,
        public Level $level,
        public ?string $previous,
    ) {}
}
