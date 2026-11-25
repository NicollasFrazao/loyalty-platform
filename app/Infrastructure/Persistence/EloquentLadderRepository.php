<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Level\CashbackRate;
use App\Domain\Level\InvalidLadder;
use App\Domain\Level\Level;
use App\Domain\Level\LevelLadder;
use App\Domain\Level\LevelMetric;
use App\Domain\Level\LadderRepository;
use App\Domain\Shared\InvalidName;
use App\Domain\Shared\Name;
use App\Domain\Tenancy\CompanyId;
use App\Infrastructure\Persistence\Eloquent\LevelLadderRecord;
use App\Infrastructure\Persistence\Eloquent\LevelRecord;
use Illuminate\Database\ConnectionInterface;
use UnexpectedValueException;
use ValueError;

/**
 * Each company's ladder in `level_ladders` and its levels in `levels`, written in one transaction.
 */
final readonly class EloquentLadderRepository implements LadderRepository
{
    /**
     * Takes the connection the ladder is written in.
     */
    public function __construct(private ConnectionInterface $connection) {}

    /**
     * The company's ladder, or the default one.
     *
     * @throws UnexpectedValueException when the rows hold a value no form writes.
     */
    public function for(CompanyId $company): LevelLadder
    {
        $record = LevelLadderRecord::query()->with('levels')->find($company->value);

        if (! $record instanceof LevelLadderRecord) {
            return LevelLadder::default();
        }

        try {
            return LevelLadder::of(
                LevelMetric::from($record->metric),
                ...$record->levels->map(static fn (LevelRecord $level): Level => new Level(
                    Name::from($level->name),
                    $level->threshold,
                    CashbackRate::of($level->cashback_basis_points),
                ))->all(),
            );
        } catch (InvalidLadder|InvalidName|ValueError $invalid) {
            throw new UnexpectedValueException("The levels of company {$company->value} hold a value no form writes.", 0, $invalid);
        }
    }

    /**
     * Replaces the company's levels.
     */
    public function save(CompanyId $company, LevelLadder $ladder): void
    {
        $this->connection->transaction(static function () use ($company, $ladder): void {
            LevelLadderRecord::query()->updateOrCreate(['company_id' => $company->value], ['metric' => $ladder->metric->value]);
            LevelRecord::query()->where('company_id', $company->value)->delete();

            foreach ($ladder->levels() as $position => $level) {
                LevelRecord::query()->create([
                    'company_id' => $company->value,
                    'position' => $position,
                    'name' => $level->name->value,
                    'threshold' => $level->threshold,
                    'cashback_basis_points' => $level->cashback->basisPoints,
                ]);
            }
        });
    }
}
