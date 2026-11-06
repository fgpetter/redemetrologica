<?php

namespace App\Livewire\PainelCliente;

use App\Actions\BuscaCepAction;
use App\Actions\Financeiro\GerarLancamentoInterlabAction;
use App\Actions\Interlab\InscricaoInterlabAction;
use App\Actions\NotifyInscricaoInterlabAction;
use App\Enums\EsferaGovernamental;
use App\Enums\FormaPagamentoCobranca;
use App\Helpers\Telefone;
use App\Livewire\Forms\InscricaoLaboratorioForm;
use App\Models\AgendaInterlabValor;
use App\Models\InterlabAnalista;
use App\Models\InterlabInscrito;
use App\Models\InterlabLaboratorio;
use App\Models\Pessoa;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class LabInscritos extends Component
{
    public $inscritos = [];

    #[Locked]
    public $empresaId = null;

    #[Locked]
    public $editingId = null;

    public $laboratorios_disponiveis = [];

    public $selecionadoId = null;

    public InscricaoLaboratorioForm $form;

    public $solicitar_certificado = false;

    public $informacoes_inscricao = '';

    #[Locked]
    public $interlab;

    #[Locked]
    public array $dadosCobranca = [];

    public $valores_inscricao;

    public $isVisible = false;

    public $numero_analistas = 0;

    #[Locked]
    public $requer_analistas = false;

    public function mount(): void
    {
        $this->interlab = session('interlab');
        $this->valores_inscricao = AgendaInterlabValor::where('agenda_interlab_id', $this->interlab->id)->get();
        $this->requer_analistas = ($this->interlab->interlab->avaliacao ?? null) === 'ANALISTA';

        if ($this->requer_analistas) {
            $inscricao = InterlabInscrito::where('pessoa_id', request()->user()->pessoa->id)
                ->where('agenda_interlab_id', $this->interlab->id)
                ->latest('id')
                ->first();

            if ($inscricao) {
                $this->carregarAnalistasInscricao($inscricao->id);
            }
        }

        $this->resetForm();
    }

    #[On('empresaSaved')]
    public function setEmpresa(int $empresa_id, ?array $dados_cobranca = null): void
    {
        $empresa = Pessoa::query()->where('tipo_pessoa', 'PJ')->findOrFail($empresa_id);
        $dados = Validator::make($dados_cobranca ?? [], [
            'forma_pagamento' => ['sometimes', Rule::enum(FormaPagamentoCobranca::class)],
            'exige_pedido_compra' => ['sometimes', 'boolean'],
            'entidade_governamental' => ['sometimes', 'boolean'],
            'esfera_governamental' => ['nullable', Rule::enum(EsferaGovernamental::class)],
        ])->validate();

        $this->empresaId = $empresa->id;
        $this->dadosCobranca = $dados;
        $this->isVisible = true;
        $this->loadInscritos();
        $this->atualizarDadosCobrancaInscritos();
        $this->loadLaboratorios();
    }

    private function atualizarDadosCobrancaInscritos(): void
    {
        if (empty($this->dadosCobranca)) {
            return;
        }

        foreach ($this->inscritos as $inscrito) {
            app(GerarLancamentoInterlabAction::class)->atualizarDadosCobranca($inscrito, $this->dadosCobranca);
        }
    }

    #[On('novoLabInscritoSaved')]
    public function reloadInscritos(): void
    {
        $this->loadInscritos();
        $this->loadLaboratorios();
        $this->resetForm();
        $this->selecionadoId = null;
    }

    public function loadInscritos(): void
    {
        if (! $this->empresaId) {
            $this->inscritos = [];

            return;
        }

        $this->inscritos = InterlabInscrito::with('laboratorio.endereco')
            ->where('pessoa_id', request()->user()->pessoa->id)
            ->where('empresa_id', $this->empresaId)
            ->where('agenda_interlab_id', $this->interlab->id)
            ->get();
    }

    public function loadLaboratorios(): void
    {
        if ($this->empresaId) {
            $labsJaInscritos = InterlabInscrito::where('empresa_id', $this->empresaId)
                ->where('agenda_interlab_id', $this->interlab->id)
                ->where('pessoa_id', request()->user()->pessoa->id)
                ->pluck('laboratorio_id')
                ->toArray();

            $this->laboratorios_disponiveis = InterlabLaboratorio::where('empresa_id', $this->empresaId)
                ->whereNotIn('id', $labsJaInscritos)
                ->get();

            $empresaTemLabs = InterlabLaboratorio::where('empresa_id', $this->empresaId)->exists();

            if (! $empresaTemLabs && $this->selecionadoId === null && $this->editingId === null) {
                $this->selecionadoId = 'new';
            }
        }
    }

    public function selectLab($labId): void
    {
        $this->selecionadoId = $labId;
        $this->editingId = null;
        $this->resetForm();

        if ($labId !== 'new') {
            $labModel = InterlabLaboratorio::with('endereco')->where('empresa_id', $this->empresaId)->findOrFail($labId);
            if ($labModel) {
                $this->form->laboratorio = $labModel->toArray();
                $this->form->laboratorio['endereco'] = $labModel->endereco ? $labModel->endereco->toArray() : [];
                $this->aplicarSplitEnderecoNumero();
            }
        }
    }

    public function edit($inscritoId): void
    {
        $inscrito = InterlabInscrito::with(['laboratorio.endereco'])
            ->where('pessoa_id', request()->user()->pessoa->id)
            ->where('empresa_id', $this->empresaId)
            ->where('agenda_interlab_id', $this->interlab->id)
            ->findOrFail($inscritoId);
        $this->editingId = $inscritoId;
        $this->selecionadoId = null;

        $this->form->laboratorio = $inscrito->laboratorio->toArray();
        $this->form->laboratorio['endereco'] = $inscrito->laboratorio->endereco
            ? $inscrito->laboratorio->endereco->toArray()
            : [];
        $this->aplicarSplitEnderecoNumero();

        $this->form->laboratorio['responsavel_tecnico'] = $inscrito->responsavel_tecnico;
        $this->form->laboratorio['telefone'] = $inscrito->getRawOriginal('telefone');
        $this->form->laboratorio['email'] = $inscrito->email;

        $infoInscricao = $inscrito->informacoes_inscricao ?? '';

        if (str_contains($infoInscricao, 'Certificado de Desempenho solicitado.')) {
            $this->solicitar_certificado = true;
            $infoInscricao = str_replace([' | Certificado de Desempenho solicitado.', 'Certificado de Desempenho solicitado.'], '', $infoInscricao);
        } else {
            $this->solicitar_certificado = false;
        }

        $this->informacoes_inscricao = '';
        if (! empty($infoInscricao)) {
            if (preg_match('/^Blocos:.*?\.(.*)$/', $infoInscricao, $matches)) {
                $this->informacoes_inscricao = trim($matches[1]);
            } elseif (! str_starts_with(trim($infoInscricao), 'Blocos:')) {
                $this->informacoes_inscricao = trim($infoInscricao);
            }
        }

        $this->form->blocosSelecionados = [];
        $this->form->blocoSelecionado = null;

        if ($this->requer_analistas) {
            $this->carregarAnalistasInscricao($inscritoId);
        }
    }

    private function carregarAnalistasInscricao(int $inscritoId): void
    {
        $this->form->analistas = InterlabAnalista::where('interlab_inscrito_id', $inscritoId)
            ->orderBy('id')
            ->get(['nome', 'email', 'telefone'])
            ->map(fn (InterlabAnalista $analista) => [
                'nome' => $analista->nome,
                'email' => $analista->email,
                'telefone' => $analista->getRawOriginal('telefone'),
            ])
            ->values()
            ->toArray();

        $this->numero_analistas = count($this->form->analistas);
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->resetForm();
    }

    public function cancelCreate(): void
    {
        $this->selecionadoId = null;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->form->laboratorio = [
            'nome' => '',
            'responsavel_tecnico' => '',
            'telefone' => '',
            'email' => '',
            'endereco' => [
                'cep' => '', 'endereco' => '', 'numero' => '', 'complemento' => '', 'bairro' => '', 'cidade' => '', 'uf' => '',
            ],
        ];
        $this->form->blocosSelecionados = [];
        $this->form->blocoSelecionado = null;
        $this->solicitar_certificado = false;
        $this->informacoes_inscricao = '';
        $this->form->analistas = [];
        $this->numero_analistas = 0;
    }

    public function buscaCep(BuscaCepAction $buscaCepAction): void
    {
        $cep = $this->form->laboratorio['endereco']['cep'] ?? '';
        $dados = $buscaCepAction->execute($cep);

        if ($dados) {
            $this->form->laboratorio['endereco']['endereco'] = $dados['endereco'];
            $this->form->laboratorio['endereco']['bairro'] = $dados['bairro'];
            $this->form->laboratorio['endereco']['cidade'] = $dados['cidade'];
            $this->form->laboratorio['endereco']['uf'] = $dados['uf'];
        } else {
            $this->addError('form.laboratorio.endereco.cep', 'CEP não encontrado.');
        }
    }

    public function calcularValorEBlocos(): array
    {
        $valorTotal = 0;
        $descricoes = [];

        $blocosSelecionados = $this->getBlocosSelecionadosIds();
        if (! empty($blocosSelecionados)) {
            $blocos = AgendaInterlabValor::where('agenda_interlab_id', $this->interlab->id)->whereIn('id', $blocosSelecionados)->get();
            $isAssociado = $this->isAssociado;

            foreach ($blocos as $bloco) {
                if ($isAssociado && $bloco->valor_assoc) {
                    $valorTotal += (float) $bloco->valor_assoc;
                } else {
                    $valorTotal += (float) $bloco->valor;
                }
                $descricoes[] = $bloco->descricao;
            }
        }

        if ($this->solicitar_certificado) {
            $valorTotal += 300.00;
        }

        $info = ! empty($descricoes) ? 'Blocos: '.implode(', ', $descricoes).'.' : '';
        if ($this->solicitar_certificado) {
            $info .= ' | Certificado de Desempenho solicitado.';
        }

        return ['valor' => $valorTotal, 'info' => $info];
    }

    public function salvar(): void
    {
        $this->form->laboratorio['telefone'] = Telefone::paraValidacao($this->form->laboratorio['telefone'] ?? null);
        foreach ($this->form->analistas as $index => $analista) {
            $this->form->analistas[$index]['telefone'] = Telefone::paraValidacao($analista['telefone'] ?? null);
        }

        $this->numero_analistas = $this->getNumeroAnalistasSelecionado();

        try {
            $validados = $this->form->validar($this->requer_analistas, $this->numero_analistas);
        } catch (ValidationException $exception) {
            $this->dispatch('scroll-to-errors');

            throw $exception;
        }

        $blocos = $this->getBlocosSelecionadosIds();
        if (AgendaInterlabValor::where('agenda_interlab_id', $this->interlab->id)->whereIn('id', $blocos)->count() !== count(array_unique($blocos))) {
            $this->addError($this->requer_analistas ? 'form.blocoSelecionado' : 'form.blocosSelecionados', 'Seleção de bloco inválida.');

            return;
        }

        $laboratorioValidado = $validados['laboratorio'];

        $dadosCalculados = $this->calcularValorEBlocos();
        $valorFinal = $dadosCalculados['valor'];
        $infoFinal = $dadosCalculados['info'];

        $obsExtras = $this->informacoes_inscricao ?? '';
        if (! empty($obsExtras) && ! str_starts_with(trim($obsExtras), 'Blocos:')) {
            $infoFinal .= ' '.$obsExtras;
        }

        $enderecoCompleto = $this->montarEnderecoCompleto(
            $laboratorioValidado['endereco']['endereco'],
            $laboratorioValidado['endereco']['numero']
        );

        $pessoaId = request()->user()->pessoa->id;
        $laboratorioId = $this->selecionadoId === 'new' ? null : $this->selecionadoId;
        if ($this->editingId) {
            $laboratorioId = InterlabInscrito::query()
                ->where('empresa_id', $this->empresaId)
                ->where('pessoa_id', $pessoaId)
                ->where('agenda_interlab_id', $this->interlab->id)
                ->findOrFail($this->editingId)
                ->laboratorio_id;
        }

        $dados = [
            'empresa_id' => $this->empresaId,
            'pessoa_id' => $pessoaId,
            'inscrito_id' => $this->editingId ?: null,
            'laboratorio_id' => $laboratorioId,
            'laboratorio' => [
                'nome' => $laboratorioValidado['nome'],
                'responsavel_tecnico' => $laboratorioValidado['responsavel_tecnico'],
                'telefone' => $laboratorioValidado['telefone'] ?? null,
                'email' => $laboratorioValidado['email'],
                'endereco' => [
                    'cep' => $laboratorioValidado['endereco']['cep'],
                    'endereco' => $enderecoCompleto,
                    'complemento' => $laboratorioValidado['endereco']['complemento'] ?? null,
                    'bairro' => $laboratorioValidado['endereco']['bairro'],
                    'cidade' => $laboratorioValidado['endereco']['cidade'],
                    'uf' => $laboratorioValidado['endereco']['uf'],
                ],
            ],
            'valor' => $valorFinal,
            'informacoes_inscricao' => $infoFinal,
        ];

        $analistas = $validados['analistas'] ?? [];
        $inscrito = app(InscricaoInterlabAction::class)->execute($this->interlab, $dados, $analistas);

        app(NotifyInscricaoInterlabAction::class)->execute($inscrito, $this->interlab, $this->editingId);

        app(GerarLancamentoInterlabAction::class)->execute($inscrito, $valorFinal, $this->dadosCobranca ?: null);

        if ($this->editingId) {
            $this->editingId = null;
            session()->flash('success', 'Laboratório atualizado com sucesso!');
            $this->loadInscritos();
        } else {
            $this->selecionadoId = null;
            $this->dispatch('novoLabInscritoSaved');
            session()->flash('success', 'Inscrição realizada com sucesso!');
        }
        $this->dispatch('close-accordion', id: 'accordion-novo-lab');
    }

    #[Computed]
    public function isAssociado(): bool
    {
        if ($this->empresaId) {
            $empresa = Pessoa::find($this->empresaId);

            return $empresa->associado ?? false;
        }

        if ($this->editingId) {
            $inscrito = InterlabInscrito::find($this->editingId);
            if ($inscrito && $inscrito->empresa) {
                return $inscrito->empresa->associado ?? false;
            }
        }

        return false;
    }

    public function render()
    {
        return view('livewire.painel-cliente.lab-inscritos');
    }

    private function getBlocosSelecionadosIds(): array
    {
        if ($this->requer_analistas) {
            return empty($this->form->blocoSelecionado) ? [] : [(int) $this->form->blocoSelecionado];
        }

        return collect($this->form->blocosSelecionados)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();
    }

    private function getNumeroAnalistasSelecionado(): int
    {
        if (! $this->requer_analistas || empty($this->form->blocoSelecionado)) {
            return count($this->form->analistas);
        }

        return (int) AgendaInterlabValor::where('agenda_interlab_id', $this->interlab->id)->where('id', $this->form->blocoSelecionado)
            ->value('analistas');
    }

    private function aplicarSplitEnderecoNumero(): void
    {
        $partes = $this->separarEnderecoNumero($this->form->laboratorio['endereco']['endereco'] ?? '');
        $this->form->laboratorio['endereco']['endereco'] = $partes['endereco'];
        $this->form->laboratorio['endereco']['numero'] = $partes['numero'];
    }

    /**
     * @return array{endereco: string, numero: string}
     */
    private function separarEnderecoNumero(string $enderecoCompleto): array
    {
        $pos = strrpos($enderecoCompleto, ', ');
        if ($pos === false) {
            return ['endereco' => $enderecoCompleto, 'numero' => ''];
        }

        return [
            'endereco' => substr($enderecoCompleto, 0, $pos),
            'numero' => substr($enderecoCompleto, $pos + 2),
        ];
    }

    private function montarEnderecoCompleto(string $logradouro, string $numero): string
    {
        return trim($logradouro).', '.trim($numero);
    }
}
