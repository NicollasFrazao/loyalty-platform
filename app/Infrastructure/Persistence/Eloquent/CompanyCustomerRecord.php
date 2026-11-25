<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;

/**
 * A row of `company_customers`: a customer who follows a company.
 *
 * @property int $id
 * @property int $company_id
 * @property int $customer_id
 */
#[Unguarded]
#[Table('company_customers', timestamps: false)]
final class CompanyCustomerRecord extends Model {}
