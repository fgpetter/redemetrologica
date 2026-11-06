<?php

namespace App\Http\Requests;

use App\Helpers\Telefone;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class FaleconoscoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['phone' => Telefone::paraValidacao($this->input('phone'))]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => ['required', 'celular_com_ddd'],
            'message' => 'nullable|string|max:2000',
            'areas' => 'sometimes|array',
            'areas.*' => 'string',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'Informe um telefone para contato.',
            'phone.celular_com_ddd' => 'Informe um telefone válido com DDD.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        Log::channel('validation')->info('Erro de validação', [
            'user' => auth()->user() ?? null,
            'uri' => $this->fullUrl(),
            'method' => $this->route()->getActionName(),
            'errors' => $validator->errors(),
        ]);

        parent::failedValidation($validator);
    }
}
