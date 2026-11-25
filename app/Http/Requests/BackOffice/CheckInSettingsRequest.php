<?php

declare(strict_types=1);

namespace App\Http\Requests\BackOffice;

use App\Application\CheckIn\CheckInSettingsInput;
use App\Application\CheckIn\FenceInput;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The check-in settings form: the cooldown, the confirmation, and a circular fence whose three
 * fields are all filled or all left empty.
 */
final class CheckInSettingsRequest extends FormRequest
{
    /**
     * The shape of the input; the domain checks the ranges.
     *
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'cooldown_minutes' => ['required', 'integer'],
            'requires_confirmation' => ['boolean'],
            'fence_latitude' => ['nullable', 'numeric', 'required_with:fence_longitude,fence_radius_metres'],
            'fence_longitude' => ['nullable', 'numeric', 'required_with:fence_latitude,fence_radius_metres'],
            'fence_radius_metres' => ['nullable', 'integer', 'required_with:fence_latitude,fence_longitude'],
        ];
    }

    /**
     * The input of the use case.
     */
    public function toInput(): CheckInSettingsInput
    {
        return new CheckInSettingsInput(
            $this->integer('cooldown_minutes'),
            $this->boolean('requires_confirmation'),
            $this->filled('fence_latitude')
                ? new FenceInput($this->float('fence_latitude'), $this->float('fence_longitude'), $this->integer('fence_radius_metres'))
                : null,
        );
    }
}
