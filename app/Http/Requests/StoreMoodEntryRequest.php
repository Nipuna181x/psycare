<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMoodEntryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'mood_score' => ['required', 'integer', 'between:1,5'],
            'mood_tags' => ['present', 'array', 'max:10'],
            'mood_tags.*' => ['string', 'distinct:strict', Rule::in([
                'anxious', 'calm', 'stressed', 'sad', 'happy', 'angry',
                'tired', 'energetic', 'hopeful', 'overwhelmed',
            ])],
            'sleep_hours' => ['nullable', 'numeric', 'between:0,12'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['mood_tags' => $this->input('mood_tags', [])]);
    }
}
