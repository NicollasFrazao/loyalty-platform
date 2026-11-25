<?php

declare(strict_types=1);

namespace App\Domain\Tenancy;

use App\Domain\Shared\Name;
use LogicException;

/**
 * A business on the platform, the tenant: its plan, its status and the modules it enabled.
 */
final readonly class Company
{
    /**
     * The plan the company subscribes to.
     */
    public Plan $plan;

    /**
     * Whether the company is active.
     */
    public CompanyStatus $status;

    /**
     * The modules enabled, keyed by value.
     *
     * @var array<string, Module>
     */
    private array $modules;

    /**
     * Describes the company; a module its plan does not allow is refused.
     *
     * @throws ModuleNotInPlan when a module is not in the plan.
     */
    public function __construct(
        public CompanyId $id,
        public Name $name,
        Subscription $subscription,
        Module ...$modules,
    ) {
        $this->plan = $subscription->plan;
        $this->status = $subscription->status;
        $enabled = [];

        foreach ($modules as $module) {
            if (! $this->plan->allows($module)) {
                throw ModuleNotInPlan::of($module, $this->plan);
            }

            $enabled[$module->value] = $module;
        }

        $this->modules = $enabled;
    }

    /**
     * The same company with the module enabled.
     *
     * @throws ModuleNotInPlan when the plan does not include it.
     */
    public function enable(Module $module): self
    {
        return new self($this->id, $this->name, $this->subscription(), ...[...$this->modules(), $module]);
    }

    /**
     * The same company with the module disabled.
     */
    public function disable(Module $module): self
    {
        return $this->with(array_values(array_filter(
            $this->modules(),
            static fn (Module $enabled): bool => $enabled !== $module,
        )));
    }

    /**
     * Tells whether the module is enabled.
     */
    public function hasEnabled(Module $module): bool
    {
        return isset($this->modules[$module->value]);
    }

    /**
     * The enabled modules, in the order they were enabled.
     *
     * @return list<Module>
     */
    public function modules(): array
    {
        return array_values($this->modules);
    }

    /**
     * Passes when the company is active and has enabled the module: the check every module's use
     * case runs first.
     *
     * @throws CompanySuspended when the company is suspended.
     * @throws ModuleDisabled when the module is not enabled.
     */
    public function ensureServes(Module $module): void
    {
        if ($this->status === CompanyStatus::Suspended) {
            throw CompanySuspended::withId($this->id);
        }

        if (! $this->hasEnabled($module)) {
            throw ModuleDisabled::of($module, $this->id);
        }
    }

    /**
     * The same company with only these modules, all allowed already.
     *
     * @param  list<Module>  $modules
     */
    private function with(array $modules): self
    {
        try {
            return new self($this->id, $this->name, $this->subscription(), ...$modules);
        } catch (ModuleNotInPlan $impossible) {
            // A subset of modules the plan allowed is still allowed.
            throw new LogicException($impossible->getMessage(), 0, $impossible);
        }
    }

    /**
     * The company's plan and status.
     */
    private function subscription(): Subscription
    {
        return new Subscription($this->plan, $this->status);
    }
}
