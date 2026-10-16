<?php

namespace App\Livewire\Forms;

use App\Models\SuporteDemanda;
use Livewire\Attributes\Validate;
use Livewire\Form;

class SuporteDemandaForm extends Form
{
    #[Validate('required|string|max:255', as: 'título')]
    public string $titulo = '';

    #[Validate('required|string', as: 'descrição', message: [
        'required' => 'O campo descrição é obrigatório.',
    ])]
    public string $descricao = '';

    #[Validate(['participantes' => 'array', 'participantes.*' => 'integer|distinct|exists:users,id'])]
    public array $participantes = [];

    #[Validate('nullable|in:corretiva,evolutiva', as: 'classificação')]
    public ?string $classificacao = null;

    #[Validate('nullable|integer|min:0|max:999999', as: 'horas gastas')]
    public ?string $horasGastas = null;

    public function setDemanda(?SuporteDemanda $demanda): void
    {
        $this->classificacao = $demanda?->classificacao?->value;
        $this->horasGastas = $demanda?->horas_gastas === null ? null : (string) $demanda->horas_gastas;
        $this->titulo = $demanda?->titulo ?? '';
        $this->descricao = $demanda?->descricao ?? '';
        $this->participantes = $demanda?->participantes()->pluck('users.id')->all() ?? [];
    }

    /** @return array{titulo: string, descricao: string, participantes: array<int, int|string>, classificacao: ?string, horas_gastas: ?string} */
    public function dados(): array
    {
        return [...$this->only(['titulo', 'descricao', 'participantes', 'classificacao']), 'horas_gastas' => $this->horasGastas];
    }
}
