<?php

namespace App\Livewire\Forms;

use Livewire\Form;

class ContaBancariaForm extends Form
{
    public ?string $nome_banco = null;

    public ?string $cod_banco = null;

    public ?string $agencia = null;

    public ?string $conta = null;

    public function rules(): array
    {
        return [
            'nome_banco' => 'required|string|min:1|max:50',
            'cod_banco' => 'nullable|string|max:20',
            'agencia' => 'required|string|max:20',
            'conta' => 'required|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'nome_banco.required' => 'O campo nome do banco é obrigatório',
            'nome_banco.max' => 'Máximo de 50 caracteres permitido',
            'nome_banco.min' => 'O nome do banco deve ter no mínimo 1 caracteres',
            'agencia.required' => 'O campo agência é obrigatório',
            'agencia.max' => 'Máximo de 20 caracteres permitido',
            'conta.required' => 'O campo conta é obrigatório',
            'conta.max' => 'Máximo de 50 caracteres permitido',
        ];
    }
}
