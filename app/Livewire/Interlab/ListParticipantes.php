<?php

namespace App\Livewire\Interlab;

use Livewire\Component;
use App\Models\AgendaInterlab;
use App\Models\InterlabInscrito;
use Illuminate\Database\Eloquent\Collection;

class ListParticipantes extends Component
{
    // Collections que vêm “de fora” via mount()
    public  $pessoas;
    public  $agendainterlab;
    public  $intelabinscritos;           // lista “plana” de inscritos
    public  $interlabempresasinscritas;  // agrupamento por empresa

    // Para edição “inline” do campo valor
    public $editandoValor = null;
    public $novoValor;

    /**
     * mount() recebe 4 parâmetros:
     *  - $pessoas
     *  - $agendainterlab
     *  - $intelabinscritos        (Collection de todos os inscritos, já com as relações carregadas)
     *  - $interlabempresasinscritas (Collection agrupada por empresa, do jeito que o pai montou)
     *
     * Aqui apenas atribuímos tudo às propriedades sem mexer nelas depois.
     */
    public function mount(
         $pessoas,
         $agendainterlab,
         $intelabinscritos,
         $interlabempresasinscritas
    ) {
        $this->pessoas                   = $pessoas;
        $this->agendainterlab            = $agendainterlab;
        $this->intelabinscritos          = $intelabinscritos;
        $this->interlabempresasinscritas = $interlabempresasinscritas;
    }

    /**
     * Quando o usuário clica no “Valor” (texto) de um participante,
     * entramos em modo edição apenas daquele participante.
     * Não mexemos em $interlabempresasinscritas aqui.
     */
    public function editarValorParticipante($participanteId)
    {
        $this->editandoValor = $participanteId;

        // Carrega do banco apenas para obter o valor atual (caso tenha mudado),
        // mas não recarrega nem altera $interlabempresasinscritas.
        $participante = InterlabInscrito::findOrFail($participanteId);
        $this->novoValor = $participante->valor;
    }

    /**
     * Ao clicar em “Salvar”:
     *  1) Validamos e atualizamos no banco.
     *  2) Apenas alteramos em memória o valor daquele participante dentro de $intelabinscritos.
     *  3) Não mexemos em $interlabempresasinscritas — ela continua exatamente como veio no mount().
     *  4) Sai do modo edição.
     */
    public function atualizarValorParticipante($participanteId)
    {
        // 1) Validação
        $this->validate([
            'novoValor' => ['required', 'numeric', 'min:0'],
        ], [
            'novoValor.required' => 'O valor é obrigatório.',
            'novoValor.numeric'  => 'O valor deve ser numérico.',
            'novoValor.min'      => 'O valor deve ser maior ou igual a zero.',
        ]);

        // 2) Atualiza o banco
        $participante = InterlabInscrito::findOrFail($participanteId);
        $participante->update(['valor' => $this->novoValor]);

        // 3) Atualiza EM MEMÓRIA apenas a instância que já está em $intelabinscritos
        //    (assim a view “reflete” imediatamente sem refazer a coleção inteira).
        $itemNaColecao = $this->intelabinscritos->firstWhere('id', $participanteId);
        if ($itemNaColecao) {
            $itemNaColecao->valor = $this->novoValor;
        }

        // 4) Sai do modo edição
        $this->editandoValor = null;
        $this->novoValor     = null;

        session()->flash('success', 'Valor atualizado com sucesso!');
    }

    public function cancelarEdicao()
    {
        $this->editandoValor = null;
        $this->novoValor     = null;
    }

    /**
     * Aqui, no render(), retornamos OS DOIS arrays/collections exatamente
     * como vieram (ou foram ajustados) — sem recalcular nada de $interlabempresasinscritas.
     */
    public function render()
    {
        return view('livewire.interlab.list-participantes', [
            'intelabinscritos'          => $this->intelabinscritos,
            'interlabempresasinscritas' => $this->interlabempresasinscritas,
        ]);
    }
}
