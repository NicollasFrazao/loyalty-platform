<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/**
 * A check-in from the app: the phone's position, which a company with a fence asks for.
 */
final class CheckInRequest extends FormRequest
{
    /**
     * The shape of the input; the domain checks the ranges.
     *
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'latitude' => ['nullable', 'numeric', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'required_with:latitude'],
        ];
    }

    /**
     * The latitude and longitude sent, or null when the app sent none.
     *
     * @return array{float, float}|null
     */
    public function position(): ?array
    {
        return $this->filled('latitude') ? [$this->float('latitude'), $this->float('longitude')] : null;
    }
}
