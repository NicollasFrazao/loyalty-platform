<?php

declare(strict_types=1);

namespace App\Application\Level;

use App\Domain\Customer\CustomerId;
use App\Domain\Level\CustomerStandings;
use App\Domain\Level\LadderRepository;
use App\Domain\Level\Standing;
use App\Domain\Tenancy\CompanyId;
use App\Domain\Tenancy\CompanyNotFound;
use App\Domain\Tenancy\CompanyRepository;
use App\Domain\Tenancy\CompanySuspended;
use App\Domain\Tenancy\Module;
use App\Domain\Tenancy\ModuleDisabled;

/**
 * Shows a customer where they stand at a company, against the company's current ladder (use case).
 */
final readonly class ShowStanding
{
    /**
     * Takes the companies, the ladders and the standings.
     */
    public function __construct(
        private CompanyRepository $companies,
        private LadderRepository $ladders,
        private CustomerStandings $standings,
    ) {}

    /**
     * The customer's level, progress and next level at the company.
     *
     * @throws CompanyNotFound when no company has the id.
     * @throws CompanySuspended when the company is suspended.
     * @throws ModuleDisabled when the company has not enabled check-ins.
     */
    public function handle(CustomerId $customer, CompanyId $company): StandingView
    {
        $this->companies->get($company)->ensureServes(Module::CheckIn);
        $ladder = $this->ladders->for($company);
        $progress = $this->standings->progress(new Standing($company, $customer));

        return new StandingView($ladder->levelFor($progress), $ladder->metric, $progress, $ladder->nextFor($progress));
    }
}
