<?php

namespace App\Livewire\Forms;

use Livewire\Form;

class DespesaInterlabForm extends Form
{
    public function rules(): array
    {
        return [
            'fornecedorId' => ['required', 'integer', 'exists:fornecedores,id'],
            'produtos' => ['required', 'array', 'min:1'],
            'produtos.*' => ['array'],
            'produtos.*.material_servico' => ['required', 'string', 'max:255'],
            'produtos.*.fabricante' => ['nullable', 'string', 'max:191'],
            'produtos.*.cod_fabricante' => ['nullable', 'string', 'max:191'],
            'produtos.*.quantidade' => ['nullable', 'numeric', 'min:0'],
            'produtos.*.valor' => ['nullable', 'string'],
            'produtos.*.lote' => ['nullable', 'string', 'max:191'],
            'produtos.*.validade' => ['nullable', 'date_format:Y-m-d'],
            'produtos.*.data_compra' => ['nullable', 'date_format:Y-m-d'],
            'avaliacaoCusto' => ['nullable', 'integer', 'between:0,5'],
            'avaliacaoTempo' => ['nullable', 'integer', 'between:0,5'],
            'avaliacaoQualidade' => ['nullable', 'integer', 'between:0,5'],
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
