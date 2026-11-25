<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;

/**
 * A row of `check_in_settings`: a company's check-in rules.
 *
 * @property int $company_id
 * @property int $cooldown_minutes
 * @property bool $requires_confirmation
 * @property string|null $fence_latitude
 * @property string|null $fence_longitude
 * @property int|null $fence_radius_metres
 */
#[Unguarded]
#[Table('check_in_settings', key: 'company_id', incrementing: false, timestamps: false)]
final class CheckInSettingsRecord extends Model
{
    /**
     * Casts the flags and numbers.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cooldown_minutes' => 'integer',
            'requires_confirmation' => 'boolean',
            'fence_radius_metres' => 'integer',
        ];
    }
}
