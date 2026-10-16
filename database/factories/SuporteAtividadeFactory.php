<?php

namespace Database\Factories;

use App\Models\SuporteAtividade;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SuporteAtividade> */
class SuporteAtividadeFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'demanda_id' => SuporteDemandaFactory::new(),
            'owner_id' => UserFactory::new(),
            'descricao' => '<p>'.fake()->paragraph().'</p>',
        ];
    }
}
