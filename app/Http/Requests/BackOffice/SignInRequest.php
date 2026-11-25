<?php

declare(strict_types=1);

namespace App\Http\Requests\BackOffice;

use Illuminate\Foundation\Http\FormRequest;

/**
 * The back-office sign-in form: an email address and a password.
 */
final class SignInRequest extends FormRequest
{
    /**
     * The longest text the fields accept.
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
            'email' => ['required', 'string', 'max:'.self::MAX_LENGTH],
            'password' => ['required', 'string', 'max:'.self::MAX_LENGTH],
        ];
    }

    /**
     * The key the sign-in attempts are counted under: the email address and the client's address.
     */
    public function throttleKey(): string
    {
        return mb_strtolower(trim($this->string('email')->toString())).'|'.$this->ip();
    }
}
