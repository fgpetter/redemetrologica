<?php

namespace App\Livewire\Avaliacoes;

use App\Actions\GenerateDocxFromTemplateAction;
use App\Livewire\Forms\AgendaAvaliacoesOrcamentoForm;
use App\Models\AgendaAvaliacao;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;

class AgendaAvaliacoesOrcamentos extends Component
{
    public AgendaAvaliacao $avaliacao;

    public AgendaAvaliacoesOrcamentoForm $form;

    public ?string $dataInicio = null;

    public ?string $dataFim = null;

    public int $numAvaliadores = 0;

    public float $totalDiasTrabalho = 0;

    public int $numAvalTreinamento = 0;

    public string $avaliacoes = '';

    public float $numEnsaios = 0;

    public float $somaAvaliadores = 0;

    public float $somaDespesasEstimadas = 0;

    public float $somaDespesasReais = 0;

    public float $valorProposta = 0;

    public float $superavit = 0;

    public const MENSAGEM_CAMPOS_OBRIGATORIOS = 'Os campos Data Início, Data Fim e Tipo de Avaliação precisam ser válidos. Preencha os campos e clique em salvar antes de gerar um orçamento.';

    public function mount(AgendaAvaliacao $avaliacao): void
    {
        $this->avaliacao = $avaliacao->load(['areas.areaAtuacao']);
        $this->form->setAgenda($this->avaliacao);
        $this->carregarRelatorio();
    }

    public function updated(string $propertyName): void
    {
        if ($propertyName === 'form.perc_lucro') {
            $this->form->validateOnly('perc_lucro');
            $this->calculate();
            $this->avaliacao->update([
                'perc_lucro' => $this->form->perc_lucro,
                'valor_proposta' => $this->valorProposta,
                'superavit' => $this->superavit,
            ]);
        }
    }

    public function calculate(): void
    {
        $this->valorProposta = round(
            $this->somaAvaliadores
            + ($this->somaAvaliadores * ($this->form->perc_lucro / 100))
            + $this->somaDespesasEstimadas,
            2
        );

        $this->superavit = round(
            $this->valorProposta - $this->somaAvaliadores - $this->somaDespesasReais,
            2
        );
    }

    public function imprimirOrcamento()
    {
        $this->avaliacao->load('areas.avaliador.pessoa', 'TipoAvaliacao', 'laboratorio.pessoa.enderecos');
        $data = [
            'nome_laboratorio' => $this->avaliacao->laboratorio->nome_laboratorio ?? 'Não informado',

            'areas' => $this->avaliacao
                ->areas
                ->map(fn ($area) => $area->areaAtuacao->descricao ?? 'Não informado')
                ->sort()
                ->unique()
                ->implode(', '),

            'tipo_avaliacao' => $this->avaliacao->tipoAvaliacao->descricao ?? 'Não informado',
            'data_envio_proposta' => $this->avaliacao->data_envio_proposta ? Carbon::parse($this->avaliacao->data_envio_proposta)->format('d/m/Y') : 'Não informado',
            'data_inicio' => $this->avaliacao->data_inicio ? Carbon::parse($this->avaliacao->data_inicio)->format('d/m/Y') : 'Não informado',
            'data_fim' => $this->avaliacao->data_fim ? Carbon::parse($this->avaliacao->data_fim)->format('d/m/Y') : 'Não informado',
            'num_ensaios' => $this->avaliacao->num_ensaios ?? 'Não informado',
            'num_avaliadores' => $this->avaliacao->areas->pluck('avaliador_id')->unique()->count() ?? 'Não informado',
            'num_aval_treinamento' => $this->avaliacao->num_aval_treinamento ?? 'Não informado',
            'dias_trabalho' => $this->avaliacao->areas->sum('dias') ?? 'Não informado',
            'valor_proposta' => $this->avaliacao->valor_proposta ?? 'Não informado',
            'responsavel_tecnico' => $this->avaliacao->laboratorio->responsavel_tecnico ?? 'Não informado',
        ];

        // garante a repetição de linhas no template
        $blocks = [];

        // define entradas e saidas
        $labSlug = Str::slug($this->avaliacao->laboratorio->nome_laboratorio ?? 'laboratorio');
        $templatePath = storage_path('app/templates/Orçamento.docx');
        $outputRelativePath = "docs/Orçamento_{$labSlug}_".now()->timestamp.'.docx';

        try {
            // Gera o arquivo e devolve o path relativo
            $gerar = (new GenerateDocxFromTemplateAction)
                ->execute($templatePath, $data, $blocks, $outputRelativePath);

            // $fullPath = Storage::disk('public')->path($gerar);
            $fullPath = Storage::path("public/{$gerar}");

            // Resposta de download que deleta após enviar
            return response()
                ->download($fullPath, basename($fullPath))
                ->deleteFileAfterSend(true); // deleta o arquivo após enviar e mantem o action generica.

            // Resposta de download que deleta após enviar
            return response()
                ->download($fullPath, basename($fullPath))
                ->deleteFileAfterSend(true); // deleta o arquivo após enviar e mantem o action generico

        } catch (\Exception $e) {
            $this->addError('template', 'Erro ao gerar documento: '.$e->getMessage());
        }
    }

    public function gerarOrcamento()
    {
        if (! $this->dadosPrincipaisPreenchidos()) {
            $this->dispatch('show-orcamento-validation-alert', message: self::MENSAGEM_CAMPOS_OBRIGATORIOS);

            return;
        }

        $this->form->validate();
        $this->calculate();

        $this->avaliacao->update([
            'num_ensaios' => $this->numEnsaios,
            'soma_avaliadores' => $this->somaAvaliadores,
            'soma_despesas_estimadas' => $this->somaDespesasEstimadas,
            'soma_despesas_reais' => $this->somaDespesasReais,
            'perc_lucro' => $this->form->perc_lucro,
            'valor_proposta' => $this->valorProposta,
            'superavit' => $this->superavit,
            'data_envio_proposta' => $this->form->data_envio_proposta,
            'num_aval_treinamento' => $this->numAvalTreinamento,
            'observacoes_orcamento' => $this->form->observacoes_orcamento,
        ]);

        return $this->imprimirOrcamento();
    }

    public function render()
    {
        return view('livewire.avaliacoes.agenda-avaliacoes-orcamentos');
    }

    protected function carregarRelatorio(): void
    {
        $areas = $this->avaliacao->areas;

        $this->dataInicio = $this->avaliacao->data_inicio
            ? Carbon::parse($this->avaliacao->data_inicio)->format('d/m/Y')
            : null;
        $this->dataFim = $this->avaliacao->data_fim
            ? Carbon::parse($this->avaliacao->data_fim)->format('d/m/Y')
            : null;

        $this->numAvaliadores = $areas->pluck('avaliador_id')->unique()->count();
        $this->totalDiasTrabalho = (float) $areas->sum('dias');
        $this->numAvalTreinamento = $areas
            ->where('situacao', 'AVALIADOR EM TREINAMENTO')
            ->count();
        $this->avaliacoes = $areas
            ->map(fn ($area) => $area->areaAtuacao->descricao ?? 'Não informado')
            ->implode(', ');

        $this->numEnsaios = (float) $areas->sum('num_ensaios');
        $this->somaAvaliadores = (float) $areas->sum('valor_avaliador');
        $this->somaDespesasEstimadas = (float) $areas->sum('total_gastos_estim');
        $this->somaDespesasReais = (float) $areas->sum('total_gastos_reais');

        $this->calculate();
    }

    protected function dadosPrincipaisPreenchidos(): bool
    {
        return filled($this->avaliacao->data_inicio)
            && filled($this->avaliacao->data_fim)
            && filled($this->avaliacao->tipo_avaliacao_id);
    }
}
