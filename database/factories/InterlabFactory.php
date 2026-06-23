<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Interlab>
 */
class InterlabFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => 'Interlab '.fake()->unique()->word(),
            'descricao' => fake()->sentence(),
            'tipo' => 'INTERLABORATORIAL',
            'thumb' => null,
            'observacoes' => null,
        ];
    }
}
