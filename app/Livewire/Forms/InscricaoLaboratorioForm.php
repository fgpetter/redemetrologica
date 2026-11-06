<?php

namespace App\Livewire\Forms;

use Livewire\Form;

class InscricaoLaboratorioForm extends Form
{
    public array $laboratorio = [];

    public array $blocosSelecionados = [];

    public int|string|null $blocoSelecionado = null;

    public array $analistas = [];

    public function validar(bool $requerAnalistas, int $numeroAnalistas): array
    {
        $rules = [
            'laboratorio.nome' => ['required', 'string', 'max:191'],
            'laboratorio.responsavel_tecnico' => ['required', 'string', 'max:191'],
            'laboratorio.telefone' => ['nullable', 'celular_com_ddd'],
            'laboratorio.email' => ['required', 'email', 'max:191'],
            'laboratorio.endereco.cep' => ['required', 'string'],
            'laboratorio.endereco.endereco' => ['required', 'string'],
            'laboratorio.endereco.numero' => ['required', 'string', 'max:50'],
            'laboratorio.endereco.complemento' => ['nullable', 'string'],
            'laboratorio.endereco.bairro' => ['required', 'string'],
            'laboratorio.endereco.uf' => ['required', 'string', 'size:2'],
            'laboratorio.endereco.cidade' => ['required', 'string'],
        ];

        $messages = [
            'laboratorio.nome.required' => 'Preencha o campo laboratório.',
            'laboratorio.nome.max' => 'O campo laboratório deve ter no máximo :max caracteres.',
            'laboratorio.responsavel_tecnico.required' => 'Preencha o campo responsável técnico.',
            'laboratorio.responsavel_tecnico.max' => 'O campo responsável técnico deve ter no máximo :max caracteres.',
            'laboratorio.telefone.celular_com_ddd' => 'O telefone informado é inválido.',
            'laboratorio.email.required' => 'O email é obrigatório.',
            'laboratorio.email.email' => 'O email deve ser um endereço de email válido.',
            'laboratorio.endereco.cep.required' => 'Preencha o campo CEP.',
            'laboratorio.endereco.endereco.required' => 'Preencha o campo endereço.',
            'laboratorio.endereco.numero.required' => 'Preencha o campo número.',
            'laboratorio.endereco.bairro.required' => 'Preencha o campo bairro.',
            'laboratorio.endereco.cidade.required' => 'Preencha o campo cidade.',
            'laboratorio.endereco.uf.required' => 'Preencha o campo UF.',
            'laboratorio.endereco.uf.size' => 'O campo UF deve ter exatamente 2 caracteres.',
        ];

        if ($requerAnalistas) {
            $rules['blocoSelecionado'] = ['required', 'integer', 'exists:agendainterlab_valores,id'];
            $messages['blocoSelecionado.required'] = 'Selecione um bloco.';
            $messages['blocoSelecionado.integer'] = 'Seleção de bloco inválida.';
            $messages['blocoSelecionado.exists'] = 'O bloco selecionado é inválido.';
        } else {
            $rules['blocosSelecionados'] = ['required', 'array', 'min:1'];
            $rules['blocosSelecionados.*'] = ['integer', 'distinct'];
            $messages['blocosSelecionados.required'] = 'Selecione ao menos um bloco.';
            $messages['blocosSelecionados.min'] = 'Selecione ao menos um bloco.';
        }

        if ($requerAnalistas && $numeroAnalistas > 0) {
            for ($i = 0; $i < $numeroAnalistas; $i++) {
                $rules["analistas.{$i}.nome"] = ['required', 'string', 'max:191'];
                $rules["analistas.{$i}.email"] = ['required', 'email', 'max:191'];
                $rules["analistas.{$i}.telefone"] = ['required', 'celular_com_ddd'];

                $messages["analistas.{$i}.nome.required"] = 'O nome do analista '.($i + 1).' é obrigatório.';
                $messages["analistas.{$i}.email.required"] = 'O e-mail do analista '.($i + 1).' é obrigatório.';
                $messages["analistas.{$i}.email.email"] = 'O e-mail do analista '.($i + 1).' deve ser um endereço válido.';
                $messages["analistas.{$i}.telefone.required"] = 'O telefone do analista '.($i + 1).' é obrigatório.';
                $messages["analistas.{$i}.telefone.celular_com_ddd"] = 'Informe um telefone válido com DDD.';
            }
        }

        return $this->validate($rules, $messages);
    }
}
