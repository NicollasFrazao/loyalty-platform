<?php

declare(strict_types=1);

namespace App\Http\Requests\BackOffice;

use App\Application\Tenancy\StaffInput;
use App\Domain\Tenancy\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use UnexpectedValueException;

/**
 * The new-staff-member form: a name, an email address, a role and the first password, twice. The
 * company is the acting staff member's, never a field.
 */
final class StaffRequest extends FormRequest
{
    /**
     * The longest text the fields accept; the domain allows less.
     */
    private const int MAX_LENGTH = 255;

    /**
     * The shape of the input.
     *
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:'.self::MAX_LENGTH],
            'email' => ['required', 'string', 'max:'.self::MAX_LENGTH],
            'role' => ['required', Rule::enum(Role::class)],
            'password' => ['required', 'string', 'max:'.self::MAX_LENGTH, 'confirmed'],
        ];
    }

    /**
     * The input of the use case.
     *
     * @throws UnexpectedValueException when the validated role is missing.
     */
    public function toInput(): StaffInput
    {
        return new StaffInput(
            $this->string('name')->toString(),
            $this->string('email')->toString(),
            $this->enum('role', Role::class) ?? throw new UnexpectedValueException('The validated role is missing.'),
            $this->string('password')->toString(),
        );
    }
}
