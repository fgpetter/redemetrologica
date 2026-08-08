<?php

namespace App\Livewire\Forms;

use App\Models\AgendaAvaliacao;
use Livewire\Attributes\Validate;
use Livewire\Form;

class AgendaAvaliacoesOrcamentoForm extends Form
{
    #[Validate('required|numeric|min:0', message: [
        'required' => 'O percentual de lucro é obrigatório.',
        'numeric' => 'O percentual de lucro deve ser um número.',
        'min' => 'O percentual de lucro não pode ser negativo.',
    ])]
    public $perc_lucro = 15;

    #[Validate('required|date|after_or_equal:today', message: [
        'required' => 'A data de envio da proposta é obrigatória.',
        'date' => 'Informe uma data válida.',
        'after_or_equal' => 'A data deve ser hoje ou futura.',
    ])]
    public ?string $data_envio_proposta = null;

    #[Validate('nullable|string|max:500', message: [
        'max' => 'Máximo de 500 caracteres.',
    ])]
    public ?string $observacoes_orcamento = null;

    public function setAgenda(AgendaAvaliacao $avaliacao): void
    {
        $this->perc_lucro = $avaliacao->perc_lucro ?? 15;
        $this->data_envio_proposta = $avaliacao->data_envio_proposta;
        $this->observacoes_orcamento = $avaliacao->observacoes_orcamento;
    }

    /**
     * @return array{perc_lucro: mixed, data_envio_proposta: ?string, observacoes_orcamento: ?string}
     */
    public function toArray(): array
    {
        return [
            'perc_lucro' => $this->perc_lucro,
            'data_envio_proposta' => $this->data_envio_proposta,
            'observacoes_orcamento' => $this->observacoes_orcamento,
        ];
    }
}
