<?php

declare(strict_types=1);

namespace Tests\Support\Customer;

use App\Domain\Customer\CustomerId;
use App\Domain\Customer\Following;
use App\Domain\Tenancy\CompanyId;
use DateTimeImmutable;

/**
 * Keeps who follows which company in memory, in the order they followed.
 */
final class InMemoryFollowing implements Following
{
    /**
     * The pairs, in the order they were made.
     *
     * @var list<array{customer: int, company: int}>
     */
    private array $pairs = [];

    /**
     * Adds the pair, unless it is there.
     */
    public function follow(CustomerId $customer, CompanyId $company, DateTimeImmutable $at): void
    {
        $pair = ['customer' => $customer->value, 'company' => $company->value];

        if (in_array($pair, $this->pairs, true)) {
            return;
        }

        $this->pairs[] = $pair;
    }

    /**
     * Drops the pair.
     */
    public function unfollow(CustomerId $customer, CompanyId $company): void
    {
        $this->pairs = array_values(array_filter(
            $this->pairs,
            static fn (array $pair): bool => $pair !== ['customer' => $customer->value, 'company' => $company->value],
        ));
    }

    /**
     * The companies of the customer.
     *
     * @return list<CompanyId>
     */
    public function companiesOf(CustomerId $customer): array
    {
        $companies = [];

        foreach ($this->pairs as $pair) {
            if ($pair['customer'] !== $customer->value) {
                continue;
            }

            $companies[] = new CompanyId($pair['company']);
        }

        return $companies;
    }

    /**
     * Drops every pair of the customer.
     */
    public function forget(CustomerId $customer): void
    {
        $this->pairs = array_values(array_filter($this->pairs, static fn (array $pair): bool => $pair['customer'] !== $customer->value));
    }
}
