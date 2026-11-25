<?php

declare(strict_types=1);

namespace App\Domain\Tenancy;

use App\Domain\Shared\InvalidName;
use App\Domain\Shared\Name;

/**
 * Who a staff member is: their name and the email address they sign in with.
 */
final readonly class StaffProfile
{
    /**
     * Pairs the name with the address.
     */
    public function __construct(
        public Name $name,
        public EmailAddress $email,
    ) {}

    /**
     * Reads the name and the address as typed.
     *
     * @throws InvalidStaffData when the name is blank or too long, or the address is not one.
     */
    public static function from(string $name, string $email): self
    {
        try {
            $staffName = Name::from($name);
        } catch (InvalidName $invalid) {
            throw InvalidStaffData::of(StaffField::Name, $invalid->getMessage());
        }

        return new self($staffName, EmailAddress::from($email));
    }
}
