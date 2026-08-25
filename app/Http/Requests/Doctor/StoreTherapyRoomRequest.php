<?php

namespace App\Http\Requests\Doctor;

use App\Models\TherapyRoom;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTherapyRoomRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'topic' => ['nullable', 'string'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:240'],
            'patient_ids' => ['required', 'array', 'min:1', 'max:'.TherapyRoom::MAX_PARTICIPANTS],
            'patient_ids.*' => ['integer', 'distinct'],
        ];
    }
}
