<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Customer\Customer;
use App\Domain\Customer\CustomerId;
use App\Domain\Customer\CustomerProfile;
use App\Domain\Customer\Document;
use App\Domain\Customer\InvalidCustomerData;
use App\Domain\Customer\PhoneNumber;
use App\Domain\Shared\InvalidName;
use App\Domain\Shared\Name;
use App\Infrastructure\Persistence\Eloquent\CustomerRecord;
use DateTimeImmutable;
use UnexpectedValueException;

/**
 * Maps a row of `customers` to the domain's customer, and the profile back to columns, for
 * {@see EloquentCustomerRepository}.
 */
final class CustomerRows
{
    /**
     * The customer a row stands for.
     *
     * @throws UnexpectedValueException when the row holds a value no form writes, or no verified phone.
     */
    public static function toCustomer(CustomerRecord $record): Customer
    {
        try {
            return new Customer(
                new CustomerId($record->id),
                PhoneNumber::from($record->phone ?? ''),
                new CustomerProfile(
                    $record->name === null ? null : Name::from($record->name),
                    self::day($record->birth_date),
                    $record->document === null ? null : Document::from($record->document),
                ),
                $record->phone_verified_at ?? throw new UnexpectedValueException("The customer {$record->id} has no verified phone."),
            );
        } catch (InvalidCustomerData|InvalidName $invalid) {
            throw new UnexpectedValueException("The customer {$record->id} holds a value no form writes.", 0, $invalid);
        }
    }

    /**
     * The columns of a profile.
     *
     * @return array{name: string|null, birth_date: string|null, document: string|null}
     */
    public static function columns(CustomerProfile $profile): array
    {
        return [
            'name' => $profile->name?->value,
            'birth_date' => $profile->birthDate?->format('Y-m-d'),
            'document' => $profile->document?->digits,
        ];
    }

    /**
     * The calendar day of a date column, as the domain builds it.
     */
    private static function day(?DateTimeImmutable $date): ?DateTimeImmutable
    {
        if (! $date instanceof DateTimeImmutable) {
            return null;
        }

        $day = DateTimeImmutable::createFromFormat('!Y-m-d', $date->format('Y-m-d'));

        return $day === false ? null : $day;
    }
}
