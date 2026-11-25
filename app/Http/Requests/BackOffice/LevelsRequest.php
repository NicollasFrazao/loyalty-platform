<?php

declare(strict_types=1);

namespace App\Http\Requests\BackOffice;

use App\Application\Level\LevelInput;
use App\Domain\Level\LevelLadder;
use App\Domain\Level\LevelMetric;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use UnexpectedValueException;

/**
 * The levels form: the metric and up to ten rows of name, threshold and cashback; a row without a
 * name is left out.
 */
final class LevelsRequest extends FormRequest
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
            'metric' => ['required', Rule::enum(LevelMetric::class)],
            'levels' => ['required', 'array', 'max:'.LevelLadder::MAX_LEVELS],
            'levels.*.name' => ['nullable', 'string', 'max:'.self::MAX_LENGTH],
            'levels.*.threshold' => ['nullable', 'integer', 'min:0'],
            'levels.*.cashback' => ['nullable', 'string', 'max:'.self::MAX_LENGTH],
        ];
    }

    /**
     * The metric chosen.
     *
     * @throws UnexpectedValueException when the validated metric is missing.
     */
    public function metric(): LevelMetric
    {
        return $this->enum('metric', LevelMetric::class) ?? throw new UnexpectedValueException('The validated metric is missing.');
    }

    /**
     * The rows with a name, in order.
     *
     * @return list<LevelInput>
     */
    public function levels(): array
    {
        $levels = [];

        foreach ($this->array('levels') as $row) {
            if (
                ! is_array($row)
                || ! is_string($row['name'] ?? null)
                || trim($row['name']) === ''
            ) {
                continue;
            }

            $levels[] = new LevelInput($row['name'], (int) ($row['threshold'] ?? 0), is_string($row['cashback'] ?? null) ? $row['cashback'] : '0');
        }

        return $levels;
    }
}
