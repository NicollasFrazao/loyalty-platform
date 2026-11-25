<?php

declare(strict_types=1);

namespace App\Application\Customer;

use App\Domain\Customer\Customer;
use App\Domain\Customer\CustomerId;
use App\Domain\Customer\CustomerNotFound;
use App\Domain\Customer\CustomerProfile;
use App\Domain\Customer\CustomerRepository;
use App\Domain\Customer\InvalidCustomerData;
use App\Domain\Shared\Clock;

/**
 * The signed-in customer's own account: reading it and changing the profile (use case).
 */
final readonly class ManageProfile
{
    /**
     * Takes the customers and the clock.
     */
    public function __construct(
        private CustomerRepository $customers,
        private Clock $clock,
    ) {}

    /**
     * The customer.
     *
     * @throws CustomerNotFound when the account was deleted.
     */
    public function show(CustomerId $id): Customer
    {
        return $this->customers->get($id);
    }

    /**
     * Replaces the profile with the values sent.
     *
     * @throws InvalidCustomerData when a value is refused.
     * @throws CustomerNotFound when the account was deleted.
     */
    public function update(CustomerId $id, ProfileInput $input): Customer
    {
        $profile = CustomerProfile::from($input->name, $input->birthDate, $input->document, $this->clock->now());
        $this->customers->saveProfile($id, $profile);

        return $this->customers->get($id);
    }
}
