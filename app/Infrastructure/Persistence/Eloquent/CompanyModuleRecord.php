<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * A row of `company_modules`: one module a company enabled.
 */
#[Table('company_modules', timestamps: false)]
#[Fillable(['company_id', 'module'])]
final class CompanyModuleRecord extends Model {}
