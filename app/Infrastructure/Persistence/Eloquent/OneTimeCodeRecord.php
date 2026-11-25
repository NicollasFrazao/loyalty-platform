<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;

/**
 * A row of `one_time_codes`: the hash of a code sent to a phone.
 *
 * @property int $id
 * @property string $phone
 * @property string $code_hash
 * @property int $attempts
 * @property CarbonImmutable $expires_at
 * @property CarbonImmutable|null $used_at
 * @property CarbonImmutable $created_at
 */
#[Unguarded]
#[Table('one_time_codes', timestamps: false)]
final class OneTimeCodeRecord extends Model
{
    /**
     * Casts the moments.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
            'expires_at' => 'immutable_datetime',
            'used_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
        ];
    }
}
