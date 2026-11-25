<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Customer\CustomerId;
use App\Domain\Customer\Following;
use App\Domain\Tenancy\CompanyId;
use App\Infrastructure\Persistence\Eloquent\CompanyCustomerRecord;
use DateTimeImmutable;

/**
 * Who follows which company, kept in `company_customers`.
 */
final readonly class EloquentFollowing implements Following
{
    /**
     * Records the pair, unless it is there already.
     */
    public function follow(CustomerId $customer, CompanyId $company, DateTimeImmutable $at): void
    {
        CompanyCustomerRecord::query()->insertOrIgnore([
            'company_id' => $company->value,
            'customer_id' => $customer->value,
            'followed_at' => $at,
        ]);
    }

    /**
     * Deletes the pair.
     */
    public function unfollow(CustomerId $customer, CompanyId $company): void
    {
        CompanyCustomerRecord::query()->where('customer_id', $customer->value)->where('company_id', $company->value)->delete();
    }

    /**
     * The companies the customer follows, in the order they followed them.
     *
     * @return list<CompanyId>
     */
    public function companiesOf(CustomerId $customer): array
    {
        return array_values(CompanyCustomerRecord::query()
            ->where('customer_id', $customer->value)
            ->orderBy('followed_at')
            ->orderBy('id')
            ->get(['company_id'])
            ->map(static fn (CompanyCustomerRecord $row): CompanyId => new CompanyId($row->company_id))
            ->all());
    }

    /**
     * Deletes every pair of the customer.
     */
    public function forget(CustomerId $customer): void
    {
        CompanyCustomerRecord::query()->where('customer_id', $customer->value)->delete();
    }
}
