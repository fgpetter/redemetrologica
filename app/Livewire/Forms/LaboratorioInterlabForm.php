<?php

namespace App\Livewire\Forms;

use Livewire\Form;

class LaboratorioInterlabForm extends Form
{
    public $empresa_id;

    public $nome;

    public $cep;

    public $endereco;

    public $complemento;

    public $bairro;

    public $cidade;

    public $uf;

    public function rules(): array
    {
        return [
            'empresa_id' => 'required|exists:pessoas,id',
            'nome' => 'required|string|max:255',
            'cep' => 'required|string|max:9',
            'endereco' => 'required|string|max:255',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
            'uf' => 'required|string|size:2',
        ];
    }
}
