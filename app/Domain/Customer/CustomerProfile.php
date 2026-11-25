<?php

declare(strict_types=1);

namespace App\Domain\Customer;

use App\Domain\Shared\InvalidName;
use App\Domain\Shared\Name;
use DateTimeImmutable;

/**
 * What a customer tells about themselves, all optional: a name, a birth date and a CPF.
 */
final readonly class CustomerProfile
{
    /**
     * Holds the values; use {@see CustomerProfile::from()} to read them as typed.
     */
    public function __construct(
        public ?Name $name = null,
        public ?DateTimeImmutable $birthDate = null,
        public ?Document $document = null,
    ) {}

    /**
     * Reads the values as typed; an empty text leaves the value out.
     *
     * @throws InvalidCustomerData when the name is too long, the birth date is not a past day
     *                             ("YYYY-MM-DD"), or the CPF is not valid.
     */
    public static function from(string $name, string $birthDate, string $document, DateTimeImmutable $today): self
    {
        return new self(
            trim($name) === '' ? null : self::name($name),
            trim($birthDate) === '' ? null : self::birthDate(trim($birthDate), $today),
            trim($document) === '' ? null : Document::from($document),
        );
    }

    /**
     * The name, refused as a customer field.
     *
     * @throws InvalidCustomerData when it is too long.
     */
    private static function name(string $text): Name
    {
        try {
            return Name::from($text);
        } catch (InvalidName $invalid) {
            throw InvalidCustomerData::of(CustomerField::Name, $invalid->getMessage());
        }
    }

    /**
     * A day of the calendar before today.
     *
     * @throws InvalidCustomerData when it is not "YYYY-MM-DD" or not in the past.
     */
    private static function birthDate(string $text, DateTimeImmutable $today): DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $text);

        if (
            $date === false
            || $date->format('Y-m-d') !== $text
            || $date >= $today->setTime(0, 0)
        ) {
            throw InvalidCustomerData::of(CustomerField::BirthDate, 'a past day such as 1990-05-17');
        }

        return $date;
    }
}
