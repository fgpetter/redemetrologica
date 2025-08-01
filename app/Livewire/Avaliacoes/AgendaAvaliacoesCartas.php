<?php

namespace App\Livewire\Avaliacoes;

use Livewire\Component;
use App\Models\AgendaAvaliacao;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Actions\GenerateDocxFromTemplateAction;

class AgendaAvaliacoesCartas extends Component
{
    public AgendaAvaliacao $avaliacao;




    public function gerarCartaReconhecimento()
    {
        $this->avaliacao->load('areas.avaliador.pessoa' ,'laboratorio.laboratoriosInternos');
        $data = [
            'data_atual' => now()->format('d/m/Y'),
            'nome_laboratorio' => $this->avaliacao->laboratorio->nome_laboratorio ?? 'Não informado',
            'data_fim' => $this->avaliacao->data_fim ? \Carbon\Carbon::parse($this->avaliacao->data_fim)->format('d/m/Y') : 'Não informado',
            'data_retorno' => $this->avaliacao->retorno_fr06 ? \Carbon\Carbon::parse($this->avaliacao->retorno_fr06)->format('d/m/Y') : 'Não informado',
            'data_evidencias' => $this->avaliacao->data_acoes_corretivas ? \Carbon\Carbon::parse($this->avaliacao->data_acoes_corretivas)->format('d/m/Y') : 'Não informado',
        ];

        $areas = $this->avaliacao->areas->map(function($area) {
            return [
                'area_nome' => $area->areaAtuacao->descricao ?? 'Não informado',
            ];
        })->toArray();

        $avaliadores = $this->avaliacao->areas->map(function ($area) {
            return [
                'avaliador_nome' => $area->avaliador->pessoa->nome_razao ?? 'Não informado',
                'avaliador_email' => $area->avaliador->pessoa->email ?? 'Não informado',
            ];
        })->toArray();

        //garante a repetição de linhas no template
        $blocks = [
            'AREAS_BLOCK'  => $areas,
            'AVAL_BLOCK' => $avaliadores,
        ];


        // define entradas e saidas
        $labSlug = Str::slug($this->avaliacao->laboratorio->nome_laboratorio ?? 'laboratorio');
        $templatePath = storage_path('app/templates/Carta_de_Reconhecimento.docx');
        $outputRelativePath = "docs/Carta_de_Reconhecimento_{$labSlug}_" . now()->timestamp . ".docx";



        try {
            $gerar = (new GenerateDocxFromTemplateAction())
                ->execute($templatePath, $data, $blocks, $outputRelativePath);

            return Storage::disk('public')->download($gerar);
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