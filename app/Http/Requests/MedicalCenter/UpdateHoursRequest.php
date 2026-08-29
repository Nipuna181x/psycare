<?php

namespace App\Http\Requests\MedicalCenter;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateHoursRequest extends FormRequest
{
    /** @var array<int, string> */
    private const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'hours' => ['required', 'array', 'size:7'],
            'hours.*.day' => ['required', 'string', 'distinct', Rule::in(self::DAYS)],
            'hours.*.opens' => ['nullable', 'date_format:H:i'],
            'hours.*.closes' => ['nullable', 'date_format:H:i'],
            'hours.*.closed' => ['boolean'],
        ];
    }

    /**
     * Validate complete, same-day operating windows for every open day.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $hours = $this->input('hours');

            if (! is_array($hours)) {
                return;
            }

            foreach ($hours as $row) {
                if (! is_array($row) || filter_var($row['closed'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                    continue;
                }

                $day = is_string($row['day'] ?? null) ? $row['day'] : 'Each open day';
                $opens = $row['opens'] ?? null;
                $closes = $row['closes'] ?? null;

                if (! is_string($opens) || $opens === '' || ! is_string($closes) || $closes === '') {
                    $validator->errors()->add('hours', "{$day} requires both an opening and closing time, or it must be marked closed.");

                    continue;
                }

                if (preg_match('/^\d{2}:\d{2}$/', $opens) && preg_match('/^\d{2}:\d{2}$/', $closes) && $closes <= $opens) {
                    $validator->errors()->add('hours', "{$day}'s closing time must be later than its opening time.");
                }
            }
        }];
    }
}
