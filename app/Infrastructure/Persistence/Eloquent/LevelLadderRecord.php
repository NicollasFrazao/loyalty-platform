<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A row of `level_ladders`: the metric a company's levels are climbed by.
 *
 * @property int $company_id
 * @property string $metric
 * @property-read Collection<int, LevelRecord> $levels
 */
#[Unguarded]
#[Table('level_ladders', key: 'company_id', incrementing: false, timestamps: false)]
final class LevelLadderRecord extends Model
{
    /**
     * The ladder's levels, by position.
     *
     * @return HasMany<LevelRecord, $this>
     */
    public function levels(): HasMany
    {
        return $this->hasMany(LevelRecord::class, 'company_id', 'company_id')->orderBy('position');
    }
}
