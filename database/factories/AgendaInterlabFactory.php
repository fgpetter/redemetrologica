<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AgendaInterlab>
 */
class AgendaInterlabFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'interlab_id' => InterlabFactory::new(),
            'status' => 'CONFIRMADO',
            'certificado' => 'EMPRESA',
            'inscricao' => true,
            'site' => false,
            'destaque' => false,
            'descricao' => fake()->sentence(),
            'data_inicio' => now()->toDateString(),
            'data_fim' => now()->addDays(30)->toDateString(),
            'instrucoes_inscricao' => fake()->paragraph(),
            'ano_referencia' => (int) now()->format('Y'),
            'data_limite_inscricao' => now()->addDays(10)->toDateString(),
            'protocolo' => null,
        ];
    }
}
