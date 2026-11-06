<?php

namespace App\Livewire\Forms;

use Livewire\Form;

class ParticipanteCursoForm extends Form
{
    public $nome;

    public $email;

    public $telefone;

    public function rules(): array
    {
        return [
            'nome' => 'required|string|min:3',
            'email' => 'required|email',
            'telefone' => ['nullable', 'celular_com_ddd'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome é obrigatório.',
            'nome.min' => 'O nome deve ter no mínimo 3 caracteres.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'O e-mail informado é inválido.',
            'telefone.celular_com_ddd' => 'Informe um telefone válido com DDD.',
        ];
    }
}
