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
            'skipped' => ['sometimes', 'boolean'],
            'answers' => ['required_if:skipped,false', 'array', 'size:16'],
            'answers.*.key' => ['required_with:answers', 'string', 'distinct'],
            'answers.*.question' => ['required_with:answers', 'string'],
            'answers.*.instrument' => ['required_with:answers', 'string', 'in:phq9,gad7'],
            'answers.*.score' => ['required_with:answers', 'integer', 'min:0', 'max:3'],
            'answers.*.answer' => ['nullable', 'string', 'max:2000'],
            'answers.*.confidence' => ['nullable', 'string', 'in:high,medium,low,manual'],
            'answers.*.extracted_context' => ['nullable', 'string', 'max:2000'],
            'open_notes' => ['nullable', 'string', 'max:4000'],
        ];
    }
}
