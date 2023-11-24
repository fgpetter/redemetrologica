<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
      $tipo = fake()->randomElement(['PF', 'PJ', 'PJ']);
      $company = fake()->company;
      return [
          'uid' => config('hashing.uid'),
          'nome_razao' => $tipo == 'PJ' ? $company : fake()->name,
          'tipo_pessoa' => $tipo,
          'nome_fantasia' => $tipo == 'PJ' ? $company : '',
          'cpf_cnpj' => $tipo == 'PJ' ? rand(11111111111111, 99999999999999) : rand(11111111111, 99999999999),
          'rg_ie' => fake()->randomNumber(8, true),
          'insc_municipal' => $tipo == 'PJ' ? fake()->optional()->randomNumber(8, true) : '',
          'email' => fake()->unique()->safeEmail(),
          'telefone' => '51'.fake()->randomNumber(8, true),
          'codigo_contabil' => $tipo == 'PJ' ? fake()->randomNumber(8, true) : '',
          'end_padrao' => fake()->randomNumber(2, true),
          'end_cobranca' => fake()->randomNumber(2, true),
      ];
    }

}
