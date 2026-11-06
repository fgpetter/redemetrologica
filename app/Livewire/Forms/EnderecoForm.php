<?php

namespace App\Livewire\Forms;

use Livewire\Form;

class EnderecoForm extends Form
{
    public ?string $info = null;

    public ?string $cep = null;

    public ?string $endereco = null;

    public ?string $complemento = null;

    public ?string $bairro = null;

    public ?string $cidade = null;

    public ?string $uf = null;

    public bool $end_padrao = false;

    public function rules(): array
    {
        return [
            'end_padrao' => 'required|boolean',
            'info' => 'nullable|string|max:255',
            'cep' => 'required|string|size:9',
            'endereco' => 'required|string|max:255',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'nullable|string|max:255',
            'cidade' => 'required|string|max:255',
            'uf' => 'required|string|size:2',
        ];
    }

    public function messages(): array
    {
        return [
            'cep.required' => 'Preencha o campo CEP',
            'cep.size' => 'O CEP precisa ter 9 dígitos',
            'endereco.required' => 'Preencha o campo endereço',
            'cidade.required' => 'Preencha o campo cidade',
            'uf.required' => 'Preencha o campo UF',
            'uf.size' => 'UF deve ter 2 caracteres',
        ];
    }
}
