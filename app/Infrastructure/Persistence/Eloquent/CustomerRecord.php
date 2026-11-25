<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Carbon\CarbonImmutable;
use Illuminate\Auth\Authenticatable as AuthenticatableUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\Contracts\HasApiTokens as HasApiTokensContract;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * A row of `customers`: the account the customer app signs in to with Sanctum tokens.
 *
 * @property int $id
 * @property string|null $phone
 * @property string|null $name
 * @property CarbonImmutable|null $birth_date
 * @property string|null $document
 * @property CarbonImmutable|null $phone_verified_at
 * @property CarbonImmutable|null $deleted_at
 */
#[Unguarded]
#[Table('customers')]
final class CustomerRecord extends Model implements Authenticatable, HasApiTokensContract
{
    use AuthenticatableUser;

    /**
     * Issues and reads the customer's API tokens.
     *
     * @use HasApiTokens<PersonalAccessToken>
     */
    use HasApiTokens;

    /**
     * Casts the dates.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'immutable_date',
            'phone_verified_at' => 'immutable_datetime',
            'deleted_at' => 'immutable_datetime',
        ];
    }
}
