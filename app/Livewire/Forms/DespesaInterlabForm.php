<?php

namespace App\Livewire\Forms;

use Livewire\Form;

class DespesaInterlabForm extends Form
{
    public function rules(bool $nova = true): array
    {
        return [
            'fornecedorId' => ['required', 'integer', 'exists:fornecedores,id'],
            'dataCompra' => [$nova ? 'required' : 'nullable', 'date_format:Y-m-d'],
            'tipo' => [$nova ? 'required' : 'nullable', 'in:produto,servico'],
            'especificacao' => [$nova ? 'required' : 'nullable', 'string', 'max:255'],
            'produtos' => ['required', 'array', 'min:1'],
            'produtos.*' => ['array'],
            'produtos.*.id' => ['nullable', 'integer'],
            'produtos.*.material_servico' => ['required', 'string', 'max:255'],
            'produtos.*.fabricante' => ['nullable', 'string', 'max:191'],
            'produtos.*.cod_fabricante' => ['nullable', 'string', 'max:191'],
            'produtos.*.quantidade' => ['nullable', 'numeric', 'min:0'],
            'produtos.*.valor' => ['nullable', 'string'],
            'produtos.*.lote' => ['nullable', 'string', 'max:191'],
            'produtos.*.validade' => ['nullable', 'date_format:Y-m-d'],
            'produtos.*.nota_fiscal' => ['nullable', 'string', 'max:255'],
            'avaliacaoCusto' => ['nullable', 'integer', 'between:0,10'],
            'avaliacaoTempo' => ['nullable', 'integer', 'between:0,10'],
            'avaliacaoQualidade' => ['nullable', 'integer', 'between:0,10'],
        ];
    }

    public function messages(): array
    {
        return [
            'fornecedorId.required' => 'Selecione o fornecedor.',
            'produtos.*.material_servico.required' => 'Material/Serviço é obrigatório.',
        ];
    }
}
