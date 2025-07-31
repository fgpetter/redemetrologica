<?php

namespace App\Livewire\Avaliacoes;

use App\Actions\GenerateDocxFromTemplateAction;
use App\Models\AgendaAvaliacao;
use Livewire\Component;

class AgendaAvaliacoesCartas extends Component
{
    public AgendaAvaliacao $avaliacao;

    public function mount(AgendaAvaliacao $avaliacao)
    {
        $this->avaliacao = $avaliacao->load('areas');
    }


    public function gerarCartaReconhecimento()
    {
        $data = [
            'laboratorio' => $this->avaliacao->id ?? 'Não informado',
            'data' => now()->format('d/m/Y'),
            'outro_parametro' => 'Outro parâmetro',
            'outro_parametro2' => 'Outro parâmetro',
            'outro_parametro3' => 'Outro parâmetro',
            'outro_parametro4' => 'Outro parâmetro'
        ];

        // define onde está o template 
        $templatePath = storage_path('app/templates/Carta_de_Reconhecimento.docx');
        $outputRelativePath = 'docs/Carta_de_Reconhecimento_' . now()->timestamp . '.docx';

        // Executa a ação
        try {
            $gerar = (new GenerateDocxFromTemplateAction())
                ->execute($templatePath, $data, $outputRelativePath);
        } catch (\Exception $e) {
            $this->addError('template', 'Erro ao gerar documento: ' . $e->getMessage());
            return;
        }

    }

    /**
     * Gera a Carta de Marcação da Avaliação.
     */
    public function gerarCartaMarcacao()
    {
       
    }

    public function render()
    {
        return view('livewire.avaliacoes.agenda-avaliacoes-cartas');
    }
}