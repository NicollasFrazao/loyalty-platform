<?php

declare(strict_types=1);

namespace App\Domain\Tenancy;

/**
 * What a company subscribes to on the platform: the modules it may enable and how many staff members
 * it may have.
 */
enum Plan: string
{
    case Starter = 'starter';
    case Growth = 'growth';
    case Complete = 'complete';

    /**
     * The staff members a Starter company may have.
     */
    private const int STARTER_STAFF = 3;

    /**
     * The staff members a Growth company may have.
     */
    private const int GROWTH_STAFF = 10;

    /**
     * The staff members a Complete company may have.
     */
    private const int COMPLETE_STAFF = 50;

    /**
     * Tells whether a company on this plan may enable the module.
     */
    public function allows(Module $module): bool
    {
        return in_array($module, $this->modules(), true);
    }

    /**
     * The most staff members a company on this plan may have, the owner included.
     */
    public function staffLimit(): int
    {
        return match ($this) {
            self::Starter => self::STARTER_STAFF,
            self::Growth => self::GROWTH_STAFF,
            self::Complete => self::COMPLETE_STAFF,
        };
    }

    /**
     * The modules of the plan.
     *
     * @return list<Module>
     */
    private function modules(): array
    {
        return match ($this) {
            self::Starter => [Module::CheckIn, Module::Cashback],
            self::Growth => [Module::CheckIn, Module::Cashback, Module::Vouchers, Module::Notifications],
            self::Complete => Module::cases(),
        };
    }
}
