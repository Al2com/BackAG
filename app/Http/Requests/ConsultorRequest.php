<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConsultorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // el middleware auth:sanctum + admin ya protege la ruta
    }

    public function rules(): array
    {
        return [
            'pregunta' => ['required', 'string', 'min:3', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'pregunta.required' => 'Escribe una pregunta para el consultor.',
            'pregunta.min'      => 'La pregunta es demasiado corta.',
            'pregunta.max'      => 'La pregunta no puede superar los 500 caracteres.',
        ];
    }
}
