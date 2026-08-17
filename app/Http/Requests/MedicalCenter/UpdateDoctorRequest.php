<?php

namespace App\Http\Requests\MedicalCenter;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateDoctorRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('doctors', 'username')->ignore($this->route('doctor'))],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'specialization' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'years_experience' => ['nullable', 'integer', 'min:0', 'max:80'],
            'consultation_fee' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
