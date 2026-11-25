<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Asks for a one-time code: the phone, in international form.
 */
final class CodeRequest extends FormRequest
{
    /**
     * The longest text the phone accepts, separators included; the domain allows less.
     */
    private const int MAX_LENGTH = 30;

    /**
     * The shape of the input.
     *
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return ['phone' => ['required', 'string', 'max:'.self::MAX_LENGTH]];
    }

    /**
     * The phone as typed.
     */
    public function phone(): string
    {
        return $this->string('phone')->toString();
    }
}
