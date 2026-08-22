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
            'answers' => ['required', 'array', 'size:16'],
            'answers.*.key' => ['required', 'string', 'distinct'],
            'answers.*.question' => ['required', 'string'],
            'answers.*.instrument' => ['required', 'string', 'in:phq9,gad7'],
            'answers.*.score' => ['required', 'integer', 'min:0', 'max:3'],
            'answers.*.answer' => ['nullable', 'string', 'max:2000'],
            'answers.*.confidence' => ['nullable', 'string', 'in:high,medium,low,manual'],
            'answers.*.extracted_context' => ['nullable', 'string', 'max:2000'],
            'open_notes' => ['nullable', 'string', 'max:4000'],
        ];
    }
}
