<?php

declare(strict_types=1);

namespace App\Domain\Customer;

/**
 * A customer's CPF, the Brazilian taxpayer number: eleven digits whose last two are check digits of
 * the first nine. Optional on the profile; kept as the digits only.
 */
final readonly class Document
{
    /**
     * The digits of a CPF, the two check digits included.
     */
    private const int LENGTH = 11;

    /**
     * The digits the check digits are computed from.
     */
    private const int BASE_LENGTH = 9;

    /**
     * The modulus of the check-digit algorithm.
     */
    private const int MODULUS = 11;

    /**
     * A remainder of ten is written as the digit zero.
     */
    private const int TEN = 10;

    /**
     * Holds the digits; use {@see Document::from()} to build one.
     */
    private function __construct(public string $digits) {}

    /**
     * Reads a CPF with or without its dots and dash.
     *
     * @throws InvalidCustomerData when it is not eleven digits, all the same digit, or the check digits
     *                             do not match.
     */
    public static function from(string $text): self
    {
        $digits = (string) preg_replace('/[.\-\s]/', '', $text);

        if (
            preg_match('/^\d{11}$/', $digits) !== 1
            || str_repeat($digits[0], self::LENGTH) === $digits
            || self::withCheckDigits(substr($digits, 0, self::BASE_LENGTH)) !== $digits
        ) {
            throw InvalidCustomerData::of(CustomerField::Document, 'not a valid CPF');
        }

        return new self($digits);
    }

    /**
     * The nine base digits followed by their two check digits.
     */
    private static function withCheckDigits(string $base): string
    {
        $first = self::checkDigit($base);

        return $base.$first.self::checkDigit($base.$first);
    }

    /**
     * The check digit of the leading digits: each weighted from its count plus one down to 2.
     */
    private static function checkDigit(string $leading): int
    {
        $weight = strlen($leading) + 1;
        $sum = 0;

        foreach (str_split($leading) as $digit) {
            $sum += (int) $digit * $weight;
            $weight--;
        }

        $remainder = $sum * self::TEN % self::MODULUS;

        return $remainder === self::TEN ? 0 : $remainder;
    }
}
