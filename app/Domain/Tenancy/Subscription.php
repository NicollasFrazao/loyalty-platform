<?php

declare(strict_types=1);

namespace App\Domain\Tenancy;

/**
 * A company's standing on the platform: the plan it subscribes to and whether it is active.
 */
final readonly class Subscription
{
    /**
     * Pairs the plan with the status.
     */
    public function __construct(
        public Plan $plan,
        public CompanyStatus $status,
    ) {}
}
