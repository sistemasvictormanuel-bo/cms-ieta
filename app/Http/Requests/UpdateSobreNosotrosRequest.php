<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSobreNosotrosRequest extends FormRequest
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
            'sn_mision' => 'required|string',
            'sn_vision' => 'required|string',
            'sn_historia' => 'required|string',
            'sn_objetivos' => 'nullable',
            'sn_contenido' => 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'sn_mision.required' => 'La misión institucional es obligatoria.',
            'sn_vision.required' => 'La visión institucional es obligatoria.',
            'sn_historia.required' => 'La historia institucional es obligatoria.',
            'sn_objetivos.required' => 'Los objetivos estratégicos son obligatorios.',
        ];
    }
}
