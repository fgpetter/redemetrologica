<?php

namespace App\Livewire\Avaliacoes;

use App\Actions\Avaliacoes\CalcularOrcamentoAvaliacaoAction;
use App\Actions\Avaliacoes\GerarDocumentoOrcamentoAvaliacaoAction;
use App\Actions\Avaliacoes\SalvarOrcamentoAvaliacaoAction;
use App\Livewire\Forms\AgendaAvaliacoesOrcamentoForm;
use App\Models\AgendaAvaliacao;
use App\Support\Observabilidade\ResumoExecucao;
use Illuminate\Support\Facades\Storage;
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

    public float $nf = 0;

    public float $valorProposta = 0;

    public float $superavit = 0;

    public const MENSAGEM_CAMPOS_OBRIGATORIOS = 'Os campos Data Início, Data Fim e Tipo de Avaliação precisam ser válidos. Preencha os campos e clique em salvar antes de gerar um orçamento.';

    public function mount(AgendaAvaliacao $avaliacao): void
    {
        $this->avaliacao = $avaliacao;
        $this->form->setAgenda($this->avaliacao);
        $this->carregarRelatorio();
    }

    public function updated(string $propertyName): void
    {
        if ($propertyName === 'form.perc_lucro') {
            $this->form->validateOnly('perc_lucro');

            app(SalvarOrcamentoAvaliacaoAction::class)->execute($this->avaliacao, [
                'perc_lucro' => $this->form->perc_lucro,
            ]);

            $this->avaliacao->refresh();
            $this->carregarRelatorio((float) $this->form->perc_lucro);
        }
    }

    public function imprimirOrcamento()
    {
        $resumo = new ResumoExecucao('avaliacao.imprimirOrcamento', ['avaliacao_uid' => $this->avaliacao->uid]);

        return $resumo->executar(fn () => $this->disponibilizarOrcamento($resumo));
    }

    public function gerarOrcamento()
    {
        $resumo = new ResumoExecucao('avaliacao.imprimirOrcamento', ['avaliacao_uid' => $this->avaliacao->uid]);

        return $resumo->executar(function () use ($resumo) {
            $valido = $resumo->etapa('validar_orcamento', function (): bool {
                if (! $this->dadosPrincipaisPreenchidos()) {
                    return false;
                }

                $this->form->validate();

                return true;
            });

            if (! $valido) {
                $resumo->resultadoEtapa('validar_orcamento', 'impedido');
                $resumo->resultado('impedido', 'validacao');
                $this->dispatch('show-orcamento-validation-alert', message: self::MENSAGEM_CAMPOS_OBRIGATORIOS);

                return;
            }

            $resumo->etapa('salvar_orcamento', function (): void {
                app(SalvarOrcamentoAvaliacaoAction::class)->execute($this->avaliacao, $this->form->toArray());
                $this->avaliacao->refresh();
                $this->carregarRelatorio((float) $this->form->perc_lucro);
            });

            return $this->disponibilizarOrcamento($resumo);
        });
    }

    private function disponibilizarOrcamento(ResumoExecucao $resumo)
    {
        try {
            $path = app(GerarDocumentoOrcamentoAvaliacaoAction::class)->execute(
                $this->avaliacao,
                (float) $this->form->perc_lucro,
                $resumo,
            );

            return $resumo->etapa('disponibilizar_documento', function () use ($path) {
                $fullPath = Storage::path("public/{$path}");

                return response()
                    ->download($fullPath, basename($fullPath))
                    ->deleteFileAfterSend(true);
            });
        } catch (\Exception $e) {
            $resumo->reportar($e);
            $resumo->resultado('falha_definitiva', 'geracao_documento');
            $this->dispatch('show-error-alert', message: 'Não foi possível gerar o documento. Tente novamente.');
        }
    }

    public function render()
    {
        return view('livewire.avaliacoes.agenda-avaliacoes-orcamentos');
    }

    protected function carregarRelatorio(?float $percLucro = null): void
    {
        $orcamento = app(CalcularOrcamentoAvaliacaoAction::class)->execute(
            $this->avaliacao,
            $percLucro ?? (float) $this->form->perc_lucro,
        );

        $this->dataInicio = $orcamento['data_inicio'];
        $this->dataFim = $orcamento['data_fim'];
        $this->numAvaliadores = $orcamento['num_avaliadores'];
        $this->totalDiasTrabalho = $orcamento['total_dias_trabalho'];
        $this->numAvalTreinamento = $orcamento['num_aval_treinamento'];
        $this->avaliacoes = $orcamento['avaliacoes'];
        $this->numEnsaios = $orcamento['num_ensaios'];
        $this->somaAvaliadores = $orcamento['soma_avaliadores'];
        $this->somaDespesasEstimadas = $orcamento['soma_despesas_estimadas'];
        $this->somaDespesasReais = $orcamento['soma_despesas_reais'];
        $this->nf = $orcamento['nf'];
        $this->valorProposta = $orcamento['valor_proposta'];
        $this->superavit = $orcamento['superavit'];
    }

    protected function dadosPrincipaisPreenchidos(): bool
    {
        return filled($this->avaliacao->data_inicio)
            && filled($this->avaliacao->data_fim)
            && filled($this->avaliacao->tipo_avaliacao_id);
    }
}
