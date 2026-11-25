<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Application\Customer\ProfileInput;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The customer's profile, all of it: a name, a birth date ("YYYY-MM-DD") and a CPF, each optional;
 * a value left out or null is erased.
 */
final class ProfileRequest extends FormRequest
{
    /**
     * The longest text the fields accept; the domain allows less.
     */
    private const int MAX_LENGTH = 255;

    /**
     * The shape of the input.
     *
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:'.self::MAX_LENGTH],
            'birth_date' => ['nullable', 'string', 'max:'.self::MAX_LENGTH],
            'document' => ['nullable', 'string', 'max:'.self::MAX_LENGTH],
        ];
    }

    /**
     * The input of the use case.
     */
    public function toInput(): ProfileInput
    {
        return new ProfileInput(
            $this->string('name')->toString(),
            $this->string('birth_date')->toString(),
            $this->string('document')->toString(),
        );
    }
}
