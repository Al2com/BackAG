<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // la ruta ya está protegida (auth:sanctum + admin)
    }

    public function rules(): array
    {
        return [
            // el producto tiene que ser del propio admin
            'producto_id'     => ['required', Rule::exists('productos', 'id')->where('admin_id', $this->user()->adminId())],
            'fecha'           => ['required', 'date'],
            'cantidad'        => ['required', 'numeric', 'min:0.01'],
            'precio_unitario' => ['nullable', 'numeric', 'min:0'],
            'proveedor'       => ['nullable', 'string', 'max:150'],
            'albaran'         => ['nullable', 'string', 'max:50'],
            'notas'           => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'producto_id.required' => 'Indica el producto de la compra.',
            'cantidad.required'    => 'Indica la cantidad comprada.',
            'cantidad.min'         => 'La cantidad debe ser mayor que cero.',
            'fecha.required'       => 'Indica la fecha de la compra.',
        ];
    }
}
