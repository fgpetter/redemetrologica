<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Unidade>
 */
class UnidadeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uid' => config('hashing.uid'),
            'nome' => fake()->company(),
            'pessoa_id' => fake()->randomElement([2, 6, 8]),
            'endereco_id' => fake()->numberBetween(1,9),
            'fone' => '51'.fake()->randomNumber(9),
            'nome_responsavel' => fake()->name(),
            'email' => fake()->safeEmail(),
            'responsavel_tecnico' => fake()->name(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
