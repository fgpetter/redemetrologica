<?php

namespace App\Livewire\Avaliacoes;

use App\Actions\GenerateDocxFromTemplateAction;
use App\Models\AgendaAvaliacao;
use App\Support\Observabilidade\ResumoExecucao;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;

class AgendaAvaliacoesCartas extends Component
{
    public AgendaAvaliacao $avaliacao;

    /**
     * Gera a Carta de Reconhecimento da Avaliação.
     */
    public function gerarCartaReconhecimento()
    {
        $resumo = new ResumoExecucao('avaliacao.gerarCartaReconhecimento', ['avaliacao_uid' => $this->avaliacao->uid]);

        return $resumo->executar(function ($resumo) {
            [$templatePath, $data, $blocks, $outputRelativePath] = $resumo->etapa('preparar_documento', function (): array {
                $this->avaliacao->load('areas.avaliador.pessoa', 'laboratorio.laboratoriosInternos');
                $data = [
                    'data_atual' => now()->format('d/m/Y'),
                    'nome_laboratorio' => $this->avaliacao->laboratorio->nome_laboratorio ?? 'Não informado',
                    'data_fim' => $this->avaliacao->data_fim ? Carbon::parse($this->avaliacao->data_fim)->format('d/m/Y') : 'Não informado',
                    'data_retorno' => $this->avaliacao->retorno_fr06 ? Carbon::parse($this->avaliacao->retorno_fr06)->format('d/m/Y') : 'Não informado',
                    'data_evidencias' => $this->avaliacao->data_acoes_corretivas ? Carbon::parse($this->avaliacao->data_acoes_corretivas)->format('d/m/Y') : 'Não informado',
                ];

                $areas = $this->avaliacao->areas->map(function ($area) {
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

                // garante a repetição de linhas no template
                $blocks = [
                    'AREAS_BLOCK' => $areas,
                    'AVAL_BLOCK' => $avaliadores,
                ];

                // define entradas e saidas
                $labSlug = Str::slug($this->avaliacao->laboratorio->nome_laboratorio ?? 'laboratorio');
                $templatePath = storage_path('app/templates/Carta_de_Reconhecimento.docx');
                $outputRelativePath = "docs/Carta_de_Reconhecimento_{$labSlug}_".now()->timestamp.'.docx';

                return [$templatePath, $data, $blocks, $outputRelativePath];
            });

            try {
                $gerar = $resumo->etapa('gerar_documento', fn () => (new GenerateDocxFromTemplateAction)
                    ->execute($templatePath, $data, $blocks, $outputRelativePath));

                return $resumo->etapa('disponibilizar_documento', fn () => Storage::download("public/{$gerar}"));

            } catch (\Exception $e) {
                $resumo->reportar($e);
                $resumo->resultado('falha_definitiva', 'geracao_documento');
                $this->dispatch('show-error-alert', message: 'Não foi possível gerar o documento. Tente novamente.');

                return;
            }

        });
    }

    /**
     * Gera a Carta de Marcação da Avaliação.
     */
    public function gerarCartaMarcacao()
    {
        $resumo = new ResumoExecucao('avaliacao.gerarCartaMarcacao', ['avaliacao_uid' => $this->avaliacao->uid]);

        return $resumo->executar(function ($resumo) {
            [$templatePath, $data, $blocks, $outputRelativePath] = $resumo->etapa('preparar_documento', function (): array {
                $this->avaliacao->load('areas.avaliador.pessoa', 'TipoAvaliacao', 'laboratorio.pessoa.enderecos');
                $data = [
                    'contato' => $this->avaliacao->laboratorio->contato ?? 'Não informado',
                    'telefone' => $this->avaliacao->laboratorio->telefone ?? 'Não informado',
                    'data_atual' => now()->format('d/m/Y'),
                    'nome_laboratorio' => $this->avaliacao->laboratorio->nome_laboratorio ?? 'Não informado',

                    'endereco' => ($this->avaliacao->laboratorio->pessoa->enderecos->first()->endereco ?? ' ').', '.
                                    ($this->avaliacao->laboratorio->pessoa->enderecos->first()->complemento ?? ' ').', '.
                                    ('Bairro: '.($this->avaliacao->laboratorio->pessoa->enderecos->first()->bairro ?? ' ')).', '.
                                    ($this->avaliacao->laboratorio->pessoa->enderecos->first()->cidade ?? ' ').' - '.
                                    ($this->avaliacao->laboratorio->pessoa->enderecos->first()->uf ?? ' ').', '.
                                    ($this->avaliacao->laboratorio->pessoa->enderecos->first()->cep ?? ' '),

                    'areas' => $this->avaliacao
                        ->areas
                        ->map(fn ($area) => $area->areaAtuacao->descricao ?? 'Não informado')
                        ->sort()
                        ->unique()
                        ->implode(', '),

                    'area' => $this->avaliacao
                        ->areas
                        ->map(fn ($area) => $area->areaAtuacao->descricao ?? 'Não informado')
                        ->sort()
                        ->values()
                        ->first() ?? 'Não informado',

                    'tipo_avaliacao' => $this->avaliacao->tipoAvaliacao->descricao ?? 'Não informado',

                ];

                $areas = $this->avaliacao->areas->map(function ($area) {
                    return [
                        'area_nome' => $area->areaAtuacao->descricao ?? 'Não informado',
                    ];
                })->toArray();

                $avaliadores = $this->avaliacao->areas->map(function ($area) {
                    return [
                        'avaliador_nome' => $area->avaliador->pessoa->nome_razao ?? 'Não informado',
                        'situacao' => $area->situacao ?? 'Não informado',
                        'data_inicial' => $area->data_inicial ? Carbon::parse($area->data_inicial)->format('d/m/Y') : 'Não informado',
                        'data_final' => $area->data_final ? Carbon::parse($area->data_final)->format('d/m/Y') : 'Não informado',
                        'dias' => $area->dias ?? 'Não informado',
                        'area_de_atuacao' => $area->areaAtuacao->descricao ?? 'Não informado',
                    ];
                })->toArray();

                $diasMap = [];
                foreach ($this->avaliacao->areas as $area) {
                    if ($area->data_inicial && $area->data_final) {
                        $periodo = CarbonPeriod::create(
                            Carbon::parse($area->data_inicial),
                            '1 day',
                            Carbon::parse($area->data_final)
                        );

                        foreach ($periodo as $dataDia) {
                            // usa Y-m-d como chave para evitar duplicatas
                            $chave = $dataDia->format('Y-m-d');
                            $diasMap[$chave] = [
                                'data_semana' => $dataDia->format('d/m/Y'),
                                'dia_semana' => ucfirst($dataDia->translatedFormat('l')),
                            ];
                        }
                    }
                }
                $dias = array_values($diasMap);

                // garante a repetição de linhas no template
                $blocks = [
                    'AVAL_BLOCK' => $avaliadores,
                    'DIAS_BLOCK' => $dias,
                ];

                // define entradas e saidas
                $labSlug = Str::slug($this->avaliacao->laboratorio->nome_laboratorio ?? 'laboratorio');
                $templatePath = storage_path('app/templates/Carta_de_Marcação.docx');
                $outputRelativePath = "docs/Carta_de_Marcação_{$labSlug}_".now()->timestamp.'.docx';

                return [$templatePath, $data, $blocks, $outputRelativePath];
            });

            try {
                $gerar = $resumo->etapa('gerar_documento', fn () => (new GenerateDocxFromTemplateAction)
                    ->execute($templatePath, $data, $blocks, $outputRelativePath));

                return $resumo->etapa('disponibilizar_documento', fn () => Storage::download("public/{$gerar}"));

            } catch (\Exception $e) {
                $resumo->reportar($e);
                $resumo->resultado('falha_definitiva', 'geracao_documento');
                $this->dispatch('show-error-alert', message: 'Não foi possível gerar o documento. Tente novamente.');

                return;
            }
        });
    }

    public function render()
    {
        return view('livewire.avaliacoes.agenda-avaliacoes-cartas');
    }
}
