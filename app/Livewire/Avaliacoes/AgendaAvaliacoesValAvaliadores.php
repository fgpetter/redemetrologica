<?php
namespace App\Livewire\Avaliacoes;

use App\Models\AgendaAvaliacao;
use Livewire\Component;

class AgendaAvaliacoesValAvaliadores extends Component
{
    public $avaliacao;

    public function mount($avaliacao)
    {
        $this->avaliacao = $avaliacao->load('areas.avaliador.pessoa');
    }

    public function render()
    {
        return view('livewire.avaliacoes.agenda-avaliacoes-val-avaliadores');
    }
}