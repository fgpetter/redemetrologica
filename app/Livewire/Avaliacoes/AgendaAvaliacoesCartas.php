<?php

namespace App\Livewire\Avaliacoes;

use Livewire\Component;

class AgendaAvaliacoesCartas extends Component
{
    public $avaliacao;

    public function mount($avaliacao)
    {
        $this->avaliacao = $avaliacao;
    }
    
    public function gerarCartaReconhecimento()
    {
        dd($this->avaliacao);
    }
    
    public function gerarCartaMarcacao()
    {
        dd($this->avaliacao);
    }

    public function render()
    {
        return view('livewire.avaliacoes.agenda-avaliacoes-cartas');
    }
}