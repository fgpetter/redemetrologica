<?php

namespace Database\Factories;

use App\Enums\SuporteStatus;
use App\Models\SuporteDemanda;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SuporteDemanda> */
class SuporteDemandaFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'titulo' => fake()->sentence(),
            'descricao' => '<p>'.fake()->paragraph().'</p>',
            'status' => SuporteStatus::Aberta,
            'autor_id' => UserFactory::new(),
            'prioridade' => 0,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (SuporteDemanda $demanda): void {
            if ($demanda->prioridade === 0) {
                $demanda->update(['prioridade' => (SuporteDemanda::withTrashed()->max('prioridade') ?? 0) + 1]);
            }
            $demanda->participantes()->syncWithoutDetaching([$demanda->autor_id]);
        });
    }

    public function concluida(): static
    {
        return $this->state(['status' => SuporteStatus::Concluida]);
    }

    public function cancelada(): static
    {
        return $this->state(['status' => SuporteStatus::Cancelada]);
    }
}
