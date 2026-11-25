<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * A staff member who signs in to the back office; the Eloquent model behind Laravel's session guard.
 *
 * It lives in Infrastructure because it is a persistence detail: the staff repository maps it to the
 * domain's staff member. Its company and role are never mass-assigned: only the repository and the
 * factories write them.
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
#[UseFactory(UserFactory::class)]
final class User extends Authenticatable
{
    /**
     * Builds users for tests and seeders through {@see UserFactory}.
     *
     * @use HasFactory<UserFactory>
     */
    use HasFactory;

    use Notifiable;

    /**
     * Casts the verification date to a date and hashes the password on assignment.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
