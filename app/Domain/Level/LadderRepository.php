<?php

declare(strict_types=1);

namespace App\Domain\Level;

use App\Domain\Tenancy\CompanyId;

/**
 * Each company's levels (port; implemented in Infrastructure).
 */
interface LadderRepository
{
    /**
     * The company's ladder; the default one when it configured none.
     */
    public function for(CompanyId $company): LevelLadder;

    /**
     * Replaces the company's ladder.
     */
    public function save(CompanyId $company, LevelLadder $ladder): void;
}
