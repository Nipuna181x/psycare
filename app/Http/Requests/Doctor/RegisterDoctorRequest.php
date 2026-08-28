<?php

namespace App\Http\Requests\Doctor;

use App\Models\Doctor;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterDoctorRequest extends FormRequest
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
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.Doctor::class],
            'password' => ['required', 'confirmed', Password::defaults()],
            'license_number' => ['required', 'string', 'max:255', 'unique:'.Doctor::class],
            'phone' => ['nullable', 'string', 'max:30'],
        ];
    }
}
