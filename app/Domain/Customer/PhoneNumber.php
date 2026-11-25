<?php

declare(strict_types=1);

namespace App\Domain\Customer;

/**
 * A mobile number in international form ("+5511912345678"), as a customer types it with or without
 * spaces, dashes and brackets; the plus and the country code are required.
 */
final readonly class PhoneNumber
{
    /**
     * Holds the normalised number; use {@see PhoneNumber::from()} to build one.
     */
    private function __construct(public string $value) {}

    /**
     * Reads a number as typed.
     *
     * @throws InvalidCustomerData when it is not a plus followed by 10 to 15 digits, the first not zero.
     */
    public static function from(string $text): self
    {
        $number = (string) preg_replace('/[\s\-().]/', '', $text);

        if (preg_match('/^\+[1-9]\d{9,14}$/', $number) !== 1) {
            throw InvalidCustomerData::of(CustomerField::Phone, 'not an international mobile number');
        }

        return new self($number);
    }
}
