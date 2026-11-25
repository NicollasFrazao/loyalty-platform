<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;

/**
 * A row of `check_ins`: a customer's visit to a company.
 *
 * @property int $id
 * @property int $company_id
 * @property int $customer_id
 * @property string $status
 * @property string|null $latitude
 * @property string|null $longitude
 * @property CarbonImmutable $checked_in_at
 */
#[Unguarded]
#[Table('check_ins', timestamps: false)]
final class CheckInRecord extends Model
{
    /**
     * Casts the moment.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['checked_in_at' => 'immutable_datetime'];
    }
}
