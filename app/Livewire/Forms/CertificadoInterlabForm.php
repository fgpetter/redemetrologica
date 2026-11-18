<?php

namespace App\Livewire\Forms;

use Livewire\Form;

class CertificadoInterlabForm extends Form
{
    public string $email = '';

    public function rules(): array
    {
        return ['email' => 'required|email|max:191'];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'O email deve ser um endereço válido.',
            'email.max' => 'O email deve ter no máximo 191 caracteres.',
        ];
    }
}
