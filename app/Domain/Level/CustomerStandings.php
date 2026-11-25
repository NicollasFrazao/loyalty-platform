<?php

declare(strict_types=1);

namespace App\Domain\Level;

/**
 * Each customer's progress and recorded level at each company (port; implemented in Infrastructure
 * over the check-ins and the company's customers).
 */
interface CustomerStandings
{
    /**
     * The customer's confirmed check-ins and spend at the company.
     */
    public function progress(Standing $standing): Progress;

    /**
     * The name of the level last recorded for the customer at the company, if any.
     */
    public function level(Standing $standing): ?string;

    /**
     * Records the customer's level at the company.
     */
    public function recordLevel(Standing $standing, Level $level): void;
}
