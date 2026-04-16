<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LancamentoFinanceiro>
 */
class LancamentoFinanceiroFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'data_emissao' => now()->toDateString(),
            'enviado_banco' => false,
            'consiliacao' => null,
            'documento' => null,
            'nota_fiscal' => null,
            'pessoa_id' => PessoaFactory::new(),
            'centro_custo_id' => 4,
            'plano_conta_id' => 3,
            'historico' => fake()->sentence(),
            'tipo_lancamento' => 'CREDITO',
            'valor' => 100.00,
            'data_vencimento' => now()->addDays(5)->toDateString(),
            'modalidade_pagamento_id' => null,
            'data_pagamento' => null,
            'status' => 'PROVISIONADO',
            'observacoes' => null,
            'agenda_curso_id' => null,
            'agenda_interlab_id' => null,
            'agenda_avaliacao_id' => null,
        ];
    }
}
