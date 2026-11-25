<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Customer\Customer;
use App\Domain\Tenancy\Company;
use DateTimeInterface;

/**
 * The JSON the customer API answers with, as `docs/api/openapi.json` describes it.
 */
final class CustomerJson
{
    /**
     * A customer.
     *
     * @return array{id: int, phone: string, phone_verified_at: string, name: string|null, birth_date: string|null, document: string|null}
     */
    public static function customer(Customer $customer): array
    {
        return [
            'id' => $customer->id->value,
            'phone' => $customer->phone->value,
            'phone_verified_at' => $customer->phoneVerifiedAt->format(DateTimeInterface::ATOM),
            'name' => $customer->profile->name?->value,
            'birth_date' => $customer->profile->birthDate?->format('Y-m-d'),
            'document' => $customer->profile->document?->digits,
        ];
    }

    /**
     * A company a customer follows.
     *
     * @return array{id: int, name: string}
     */
    public static function company(Company $company): array
    {
        return ['id' => $company->id->value, 'name' => $company->name->value];
    }
}
