<?php

declare(strict_types=1);

namespace Tests\Support\Customer;

use App\Domain\Customer\Customer;
use App\Domain\Customer\CustomerId;
use App\Domain\Customer\CustomerNotFound;
use App\Domain\Customer\CustomerProfile;
use App\Domain\Customer\CustomerRepository;
use App\Domain\Customer\PhoneNumber;
use DateTimeImmutable;

/**
 * Keeps customers in memory, keyed by id; a deleted account is dropped, its id never reused.
 */
final class InMemoryCustomerRepository implements CustomerRepository
{
    /**
     * The accounts not deleted, keyed by id.
     *
     * @var array<int, Customer>
     */
    private array $customers = [];

    /**
     * The last id given.
     */
    private int $lastId = 0;

    /**
     * The customer with the phone, if any.
     */
    public function findByPhone(PhoneNumber $phone): ?CustomerId
    {
        foreach ($this->customers as $customer) {
            if ($customer->phone->value === $phone->value) {
                return $customer->id;
            }
        }

        return null;
    }

    /**
     * Adds a customer with an empty profile.
     */
    public function register(PhoneNumber $phone, DateTimeImmutable $verifiedAt): CustomerId
    {
        $id = new CustomerId(++$this->lastId);
        $this->customers[$id->value] = new Customer($id, $phone, new CustomerProfile, $verifiedAt);

        return $id;
    }

    /**
     * The customer with the id.
     *
     * @throws CustomerNotFound when there is none.
     */
    public function get(CustomerId $id): Customer
    {
        return $this->customers[$id->value] ?? throw CustomerNotFound::withId($id);
    }

    /**
     * Replaces the profile.
     *
     * @throws CustomerNotFound when there is no such customer.
     */
    public function saveProfile(CustomerId $id, CustomerProfile $profile): void
    {
        $customer = $this->get($id);
        $this->customers[$id->value] = new Customer($id, $customer->phone, $profile, $customer->phoneVerifiedAt);
    }

    /**
     * Drops the customer.
     *
     * @throws CustomerNotFound when there is no such customer.
     */
    public function anonymise(CustomerId $id, DateTimeImmutable $deletedAt): void
    {
        $this->get($id);
        unset($this->customers[$id->value]);
    }
}
