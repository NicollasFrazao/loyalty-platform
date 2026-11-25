<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;

/**
 * A row of `levels`: one level of a company's ladder.
 *
 * @property int $id
 * @property int $company_id
 * @property int $position
 * @property string $name
 * @property int $threshold
 * @property int $cashback_basis_points
 */
#[Unguarded]
#[Table('levels', timestamps: false)]
final class LevelRecord extends Model
{
    /**
     * Casts the numbers.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['position' => 'integer', 'threshold' => 'integer', 'cashback_basis_points' => 'integer'];
    }
}
