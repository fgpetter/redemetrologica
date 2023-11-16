<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Endereco>
 */
class EnderecoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uid' => substr(hrtime(true), -9, 9),
            'pessoa_id' => fake()->randomElement([2, 6, 8]),
            'endereco' => fake()->streetAddress(),
            'complemento' => fake()->secondaryAddress(),
            'bairro' => fake()->words(2, true),
            'cep' => fake()->postcode(),
            'cidade' => fake()->city(),
            'uf' => fake()->stateAbbr(),
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
