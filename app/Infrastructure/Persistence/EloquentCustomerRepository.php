<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Customer\Customer;
use App\Domain\Customer\CustomerId;
use App\Domain\Customer\CustomerNotFound;
use App\Domain\Customer\CustomerProfile;
use App\Domain\Customer\CustomerRepository;
use App\Domain\Customer\PhoneNumber;
use App\Infrastructure\Persistence\Eloquent\CustomerRecord;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;

/**
 * Customers kept in `customers`; a deleted account keeps its row, with no personal data and a
 * `deleted_at`, and is never found again.
 */
final readonly class EloquentCustomerRepository implements CustomerRepository
{
    /**
     * The customer who signs in with the phone, if any.
     */
    public function findByPhone(PhoneNumber $phone): ?CustomerId
    {
        $id = $this->active()->where('phone', $phone->value)->value('id');

        return is_int($id) ? new CustomerId($id) : null;
    }

    /**
     * Creates the customer; when another request just did, returns theirs.
     *
     * @throws UniqueConstraintViolationException when the phone is taken and still not found, which
     *                                            no request does.
     */
    public function register(PhoneNumber $phone, DateTimeImmutable $verifiedAt): CustomerId
    {
        $record = new CustomerRecord;
        $record->forceFill(['phone' => $phone->value, 'phone_verified_at' => $verifiedAt]);

        try {
            $record->save();
        } catch (UniqueConstraintViolationException $taken) {
            return $this->findByPhone($phone) ?? throw $taken;
        }

        return new CustomerId($record->id);
    }

    /**
     * Loads one customer.
     *
     * @throws CustomerNotFound when no customer has the id, or the account was deleted.
     */
    public function get(CustomerId $id): Customer
    {
        return CustomerRows::toCustomer($this->record($id));
    }

    /**
     * Replaces the customer's profile.
     *
     * @throws CustomerNotFound when no customer has the id, or the account was deleted.
     */
    public function saveProfile(CustomerId $id, CustomerProfile $profile): void
    {
        $this->record($id)->forceFill(CustomerRows::columns($profile))->save();
    }

    /**
     * Erases the personal data and marks the account deleted.
     *
     * @throws CustomerNotFound when no customer has the id, or the account was deleted.
     */
    public function anonymise(CustomerId $id, DateTimeImmutable $deletedAt): void
    {
        $this->record($id)->forceFill([
            ...CustomerRows::columns(new CustomerProfile),
            'phone' => null,
            'phone_verified_at' => null,
            'deleted_at' => $deletedAt,
        ])->save();
    }

    /**
     * The row of an account not deleted.
     *
     * @throws CustomerNotFound when there is none.
     */
    private function record(CustomerId $id): CustomerRecord
    {
        return $this->active()->find($id->value) ?? throw CustomerNotFound::withId($id);
    }

    /**
     * The rows of the accounts not deleted.
     *
     * @return Builder<CustomerRecord>
     */
    private function active(): Builder
    {
        return CustomerRecord::query()->whereNull('deleted_at');
    }
}
