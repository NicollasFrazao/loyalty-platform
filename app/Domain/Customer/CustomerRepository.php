<?php

declare(strict_types=1);

namespace App\Domain\Customer;

use DateTimeImmutable;

/**
 * Where customers are kept (port; implemented in Infrastructure). A deleted account is never found.
 */
interface CustomerRepository
{
    /**
     * The customer who signs in with the phone, if any.
     */
    public function findByPhone(PhoneNumber $phone): ?CustomerId;

    /**
     * Creates the customer of a phone just verified.
     */
    public function register(PhoneNumber $phone, DateTimeImmutable $verifiedAt): CustomerId;

    /**
     * Loads one customer.
     *
     * @throws CustomerNotFound when no customer has the id, or the account was deleted.
     */
    public function get(CustomerId $id): Customer;

    /**
     * Replaces the customer's profile.
     *
     * @throws CustomerNotFound when no customer has the id, or the account was deleted.
     */
    public function saveProfile(CustomerId $id, CustomerProfile $profile): void;

    /**
     * Erases the phone, the name, the birth date and the CPF and marks the account deleted; the row
     * and its id stay, so the ledger of the account still adds up.
     *
     * @throws CustomerNotFound when no customer has the id, or the account was deleted.
     */
    public function anonymise(CustomerId $id, DateTimeImmutable $deletedAt): void;
}
