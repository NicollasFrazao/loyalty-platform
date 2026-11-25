<?php

declare(strict_types=1);

namespace App\Application\Level;

use App\Application\Tenancy\ActingStaff;
use App\Domain\Level\CashbackRate;
use App\Domain\Level\InvalidLadder;
use App\Domain\Level\LadderRepository;
use App\Domain\Level\Level;
use App\Domain\Level\LevelLadder;
use App\Domain\Level\LevelMetric;
use App\Domain\Shared\InvalidName;
use App\Domain\Shared\Name;
use App\Domain\Tenancy\NotAllowed;

/**
 * The levels of the acting staff member's company: owners and managers set the ladder (use case).
 * A customer's level follows the new ladder on their next check-in or purchase; their standing is
 * always read against the current ladder.
 */
final readonly class ConfigureLevels
{
    /**
     * Takes the ladders.
     */
    public function __construct(private LadderRepository $ladders) {}

    /**
     * The company's ladder.
     */
    public function show(ActingStaff $acting): LevelLadder
    {
        return $this->ladders->for($acting->company->id);
    }

    /**
     * Replaces the company's ladder.
     *
     * @param  list<LevelInput>  $levels  The levels, from the first.
     *
     * @throws NotAllowed when the role cannot configure the company.
     * @throws InvalidLadder when the levels do not form a ladder or a name is not valid.
     */
    public function update(ActingStaff $acting, LevelMetric $metric, array $levels): LevelLadder
    {
        $acting->ensureCanConfigure();
        $ladder = LevelLadder::of($metric, ...array_map(self::level(...), $levels));
        $this->ladders->save($acting->company->id, $ladder);

        return $ladder;
    }

    /**
     * The level of a form row.
     *
     * @throws InvalidLadder when the name or the cashback is not valid.
     */
    private static function level(LevelInput $input): Level
    {
        try {
            $name = Name::from($input->name);
        } catch (InvalidName) {
            throw InvalidLadder::because('each level needs a name of up to '.Name::MAX_LENGTH.' characters');
        }

        if ($input->threshold < 0) {
            throw InvalidLadder::because('a threshold cannot be negative');
        }

        return new Level($name, $input->threshold, CashbackRate::fromPercent($input->cashbackPercent));
    }
}
