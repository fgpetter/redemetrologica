<?php

namespace App\Livewire\Avaliacoes;

use App\Models\AgendaAvaliacao;
use Livewire\Component;

class AgendaAvaliacoesOrcamentos extends Component
{
    public AgendaAvaliacao $avaliacao;

    public $num_ensaios;
    public $soma_avaliadores;
    public $soma_despesas_estimadas;
    public $soma_despesas_reais;
    public $perc_lucro = 15;
    public $valor_proposta;
    public $NF;
    public $superavit;
    public $data_envio_proposta;
    public $num_aval_treinamento;
    public $observacoes_orcamento;

    protected $rules = [
        'perc_lucro' => 'required|numeric|min:0',
        'data_envio_proposta' => 'required|date|after_or_equal:today',
        'num_aval_treinamento' => 'nullable|integer|min:0',
        'observacoes_orcamento' => 'nullable|string|max:500',
    ];

    protected $messages = [
        'perc_lucro.required' => 'O percentual de lucro é obrigatório.',
        'perc_lucro.numeric' => 'O percentual de lucro deve ser um número.',
        'perc_lucro.min' => 'O percentual de lucro não pode ser negativo.',
        'data_envio_proposta.required' => 'A data de envio da proposta é obrigatória.',
        'data_envio_proposta.date' => 'Informe uma data válida.',
        'data_envio_proposta.after_or_equal' => 'A data deve ser hoje ou futura.',
        'num_aval_treinamento.integer' => 'O número de avaliadores de treinamento deve ser inteiro.',
        'num_aval_treinamento.min' => 'Mínimo de 0 avaliadores de treinamento.',
        'observacoes_orcamento.max' => 'Máximo de 500 caracteres.',
    ];

    public function mount(AgendaAvaliacao $avaliacao)
    {
        $this->avaliacao = $avaliacao->load('areas');
        $this->num_ensaios = $this->avaliacao->areas->sum('num_ensaios');
        $this->soma_avaliadores = $this->avaliacao->areas->sum('valor_avaliador');
        $this->soma_despesas_estimadas = $this->avaliacao->areas->sum('total_gastos_estim');
        $this->soma_despesas_reais = $this->avaliacao->areas->sum('total_gastos_reais');

        
        $this->perc_lucro = $this->avaliacao->perc_lucro ?? $this->perc_lucro;
        $this->data_envio_proposta = $this->avaliacao->data_envio_proposta;
        $this->num_aval_treinamento = $this->avaliacao->num_aval_treinamento;
        $this->observacoes_orcamento = $this->avaliacao->observacoes_orcamento;

        $this->calculate();
    }

    public function updated($field)
    {
        
        $this->validateOnly($field);

        
        if ($field === 'perc_lucro') {
            $this->calculate();
        }
    }

    public function calculate()
    {
        $this->valor_proposta = round(
            $this->soma_avaliadores +
                ($this->soma_avaliadores * ($this->perc_lucro / 100)) +
                $this->soma_despesas_estimadas, 2
        );

        $this->superavit = round(
            $this->valor_proposta - $this->soma_avaliadores - $this->soma_despesas_reais, 2);
    }

    public function gerarOrcamento()
    {
        
        $this->validate();

        $this->avaliacao->update([
            'num_ensaios' => $this->num_ensaios,
            'soma_avaliadores' => $this->soma_avaliadores,
            'soma_despesas_estimadas' => $this->soma_despesas_estimadas,
            'soma_despesas_reais' => $this->soma_despesas_reais,
            'perc_lucro' => $this->perc_lucro,
            'valor_proposta' => $this->valor_proposta,
            'superavit' => $this->superavit,
            'data_envio_proposta' => $this->data_envio_proposta,
            'num_aval_treinamento' => $this->num_aval_treinamento,
            'observacoes_orcamento' => $this->observacoes_orcamento,
        ]);

        
        session()->flash('success', 'Orçamento gerado com sucesso');
        $this->redirect(url()->previous());
    }

    public function render()
    {
        return view('livewire.avaliacoes.agenda-avaliacoes-orcamentos');
    }
}
