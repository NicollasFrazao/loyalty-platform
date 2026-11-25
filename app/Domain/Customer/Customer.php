<?php

declare(strict_types=1);

namespace App\Domain\Customer;

use DateTimeImmutable;

/**
 * A person with an account in the app, who signs in with their phone; the phone was verified when the
 * account was opened.
 */
final readonly class Customer
{
    /**
     * Describes the customer.
     */
    public function __construct(
        public CustomerId $id,
        public PhoneNumber $phone,
        public CustomerProfile $profile,
        public DateTimeImmutable $phoneVerifiedAt,
    ) {}
}
