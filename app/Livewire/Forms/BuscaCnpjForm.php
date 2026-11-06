<?php

namespace App\Livewire\Forms;

use Livewire\Form;

class BuscaCnpjForm extends Form
{
    public $BuscaCnpj;

    public function rules(): array
    {
        return [
            'BuscaCnpj' => ['required', 'cnpj'],
        ];
    }

    public function messages(): array
    {
        return [
            'BuscaCnpj.cnpj' => 'Não é um CNPJ válido.',
            'BuscaCnpj.required' => 'Digite um CNPJ para cadastro.',
        ];
    }
}
