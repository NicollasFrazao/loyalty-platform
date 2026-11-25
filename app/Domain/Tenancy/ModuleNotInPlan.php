<?php

declare(strict_types=1);

namespace App\Domain\Tenancy;

use App\Domain\Shared\DomainError;

/**
 * A company tried to enable a module its plan does not include.
 */
final class ModuleNotInPlan extends DomainError
{
    /**
     * The error for the module and the plan.
     */
    public static function of(Module $module, Plan $plan): self
    {
        return new self("The {$plan->value} plan does not include the {$module->value} module.");
    }
}
