<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AiCompanionMessageRequest extends FormRequest
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
            'message' => ['required', 'string', 'max:2000'],
            'language' => ['required', 'string', 'in:en,si'],
            'history' => ['present', 'array', 'max:12'],
            'history.*.role' => ['required', 'string', 'in:user,model'],
            'history.*.text' => ['required', 'string', 'max:2000'],
        ];
    }
}
