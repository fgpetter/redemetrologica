<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pessoa>
 */
class PessoaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'nome_razao' => fake()->company(),
            'nome_fantasia' => fake()->companySuffix(),
            'cpf_cnpj' => fake()->unique()->numerify('##############'),
            'tipo_pessoa' => 'PJ',
            'rg_ie' => fake()->numerify('########'),
            'insc_municipal' => fake()->numerify('########'),
            'telefone' => fake()->numerify('###########'),
            'telefone_alt' => null,
            'celular' => fake()->numerify('###########'),
            'email' => fake()->unique()->safeEmail(),
            'email_cobranca' => fake()->unique()->safeEmail(),
            'site' => fake()->url(),
            'end_padrao' => null,
            'end_cobranca' => null,
            'associado' => false,
            'observacoes' => null,
        ];
    }

    public function withUser(): static
    {
        return $this->state(fn () => [
            'user_id' => User::factory(),
        ]);
    }
}
