<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Opens a session: the phone, the code sent to it, and a name for the device the token is for.
 */
final class TokenRequest extends FormRequest
{
    /**
     * The longest phone accepted, separators included.
     */
    private const int MAX_PHONE = 30;

    /**
     * The longest device name accepted.
     */
    private const int MAX_DEVICE = 100;

    /**
     * The shape of the input.
     *
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:'.self::MAX_PHONE],
            'code' => ['required', 'string', 'digits:6'],
            'device' => ['required', 'string', 'max:'.self::MAX_DEVICE],
        ];
    }

    /**
     * The phone as typed.
     */
    public function phone(): string
    {
        return $this->string('phone')->toString();
    }

    /**
     * The code as typed.
     */
    public function code(): string
    {
        return $this->string('code')->toString();
    }

    /**
     * The device's name.
     */
    public function device(): string
    {
        return $this->string('device')->toString();
    }
}
