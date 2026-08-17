<?php

namespace App\Http\Requests\Booking;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAssessmentRequest extends FormRequest
{
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
            'answers' => ['required', 'array', 'min:1'],
            'answers.*.key' => ['required', 'string'],
            'answers.*.question' => ['required', 'string'],
            'answers.*.answer' => ['nullable', 'string', 'max:2000'],
            'mood_rating' => ['required', 'integer', 'min:1', 'max:10'],
        ];
    }
}
