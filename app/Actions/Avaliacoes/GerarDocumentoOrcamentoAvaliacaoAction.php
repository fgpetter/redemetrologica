<?php

namespace App\Actions\Avaliacoes;

use App\Actions\GenerateDocxFromTemplateAction;
use App\Models\AgendaAvaliacao;
use Carbon\Carbon;
use Illuminate\Support\Str;

class GerarDocumentoOrcamentoAvaliacaoAction
{
    public function __construct(
        private CalcularOrcamentoAvaliacaoAction $calcularOrcamentoAvaliacaoAction,
        private GenerateDocxFromTemplateAction $generateDocxFromTemplateAction,
    ) {}

    /**
     * Monta os dados do orçamento e gera o DOCX a partir do template.
     *
     * @return string Caminho relativo do arquivo gerado no disco public
     */
    public function execute(AgendaAvaliacao $avaliacao, ?float $percLucro = null): string
    {
        $avaliacao->loadMissing([
            'areas.areaAtuacao',
            'areas.avaliador.pessoa',
            'tipoAvaliacao',
            'laboratorio.pessoa.enderecos',
        ]);

        $orcamento = $this->calcularOrcamentoAvaliacaoAction->execute($avaliacao, $percLucro);

        $data = [
            'nome_laboratorio' => $avaliacao->laboratorio->nome_laboratorio ?? 'Não informado',
            'areas' => $avaliacao
                ->areas
                ->map(fn ($area) => $area->areaAtuacao->descricao ?? 'Não informado')
                ->sort()
                ->unique()
                ->implode(', '),
            'tipo_avaliacao' => $avaliacao->tipoAvaliacao->descricao ?? 'Não informado',
            'data_envio_proposta' => $avaliacao->data_envio_proposta
                ? Carbon::parse($avaliacao->data_envio_proposta)->format('d/m/Y')
                : 'Não informado',
            'data_inicio' => $orcamento['data_inicio'] ?? 'Não informado',
            'data_fim' => $orcamento['data_fim'] ?? 'Não informado',
            'num_ensaios' => $orcamento['num_ensaios'] ?: 'Não informado',
            'num_avaliadores' => $orcamento['num_avaliadores'] ?: 'Não informado',
            'num_aval_treinamento' => $orcamento['num_aval_treinamento'] ?: 'Não informado',
            'dias_trabalho' => $orcamento['total_dias_trabalho'] ?: 'Não informado',
            'valor_proposta' => $orcamento['valor_proposta'] ?: 'Não informado',
            'responsavel_tecnico' => $avaliacao->laboratorio->responsavel_tecnico ?? 'Não informado',
        ];

        $labSlug = Str::slug($avaliacao->laboratorio->nome_laboratorio ?? 'laboratorio');
        $templatePath = storage_path('app/templates/Orçamento.docx');
        $outputRelativePath = "docs/Orçamento_{$labSlug}_".now()->timestamp.'.docx';

        return $this->generateDocxFromTemplateAction->execute(
            $templatePath,
            $data,
            [],
            $outputRelativePath,
        );
    }
}
