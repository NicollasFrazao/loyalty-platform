<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A row of `companies`, with the modules it enabled.
 */
#[Table('companies')]
#[Fillable(['name', 'plan', 'status'])]
#[UseFactory(CompanyFactory::class)]
final class CompanyRecord extends Model
{
    /**
     * Builds companies for tests and seeders through {@see CompanyFactory}.
     *
     * @use HasFactory<CompanyFactory>
     */
    use HasFactory;

    /**
     * The modules the company enabled, in the order they were enabled.
     *
     * @return HasMany<CompanyModuleRecord, $this>
     */
    public function modules(): HasMany
    {
        return $this->hasMany(CompanyModuleRecord::class, 'company_id')->orderBy('id');
    }
}
