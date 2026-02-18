<?php

namespace App\Livewire\PainelCliente;

use App\Actions\BuscaCepAction;
use App\Actions\CriarEnviarSenhaAction;
use App\Actions\Financeiro\GerarLancamentoInterlabAction;
use App\Actions\InscricaoInterlabAction;
use App\Actions\NotifyInscricaoInterlabAction;
use App\Models\AgendaInterlabValor;
use App\Models\InterlabAnalista;
use App\Models\InterlabInscrito;
use App\Models\InterlabLaboratorio;
use App\Models\Pessoa;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

class LabInscritos extends Component
{
    public $inscritos = [];

    public $empresaId = null;

    public $editingId = null;

    public $laboratorios_disponiveis = [];

    public $selecionadoId = null;

    public $laboratorio = [];

    public $blocos_selecionados = [];

    public $bloco_selecionado = null;

    public $solicitar_certificado = false;

    public $informacoes_inscricao = '';

    public $interlab;

    public $valores_inscricao;

    public $isVisible = false;

    public $numero_analistas = 0;

    public $requer_analistas = false;

    public $analistas = [];

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
    public function setEmpresa($empresa_id): void
    {
        $this->empresaId = $empresa_id;
        $this->isVisible = true;
        $this->loadInscritos();
        $this->loadLaboratorios();
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
        }
    }

    public function selectLab($labId): void
    {
        $this->selecionadoId = $labId;
        $this->editingId = null;
        $this->resetForm();

        if ($labId !== 'new') {
            $labModel = InterlabLaboratorio::with('endereco')->find($labId);
            if ($labModel) {
                $this->laboratorio = $labModel->toArray();
                $this->laboratorio['endereco'] = $labModel->endereco ? $labModel->endereco->toArray() : [];
            }
        }
    }

    public function edit($inscritoId): void
    {
        $inscrito = InterlabInscrito::with(['laboratorio.endereco'])->findOrFail($inscritoId);
        $this->editingId = $inscritoId;
        $this->selecionadoId = null;

        $this->laboratorio = $inscrito->laboratorio->toArray();
        $this->laboratorio['endereco'] = $inscrito->laboratorio->endereco
            ? $inscrito->laboratorio->endereco->toArray()
            : [];

        $this->laboratorio['responsavel_tecnico'] = $inscrito->responsavel_tecnico;
        $this->laboratorio['telefone'] = $inscrito->telefone;
        $this->laboratorio['email'] = $inscrito->email;

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

        $this->blocos_selecionados = [];
        $this->bloco_selecionado = null;

        if ($this->requer_analistas) {
            $this->carregarAnalistasInscricao($inscritoId);
        }
    }

    private function carregarAnalistasInscricao(int $inscritoId): void
    {
        $this->analistas = InterlabAnalista::where('interlab_inscrito_id', $inscritoId)
            ->orderBy('id')
            ->get(['nome', 'email', 'telefone'])
            ->map(fn (InterlabAnalista $analista) => [
                'nome' => $analista->nome,
                'email' => $analista->email,
                'telefone' => $analista->telefone,
            ])
            ->values()
            ->toArray();

        $this->numero_analistas = count($this->analistas);
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
        $this->laboratorio = [
            'nome' => '',
            'responsavel_tecnico' => '',
            'telefone' => '',
            'email' => '',
            'endereco' => [
                'cep' => '', 'endereco' => '', 'complemento' => '', 'bairro' => '', 'cidade' => '', 'uf' => '',
            ],
        ];
        $this->blocos_selecionados = [];
        $this->bloco_selecionado = null;
        $this->solicitar_certificado = false;
        $this->informacoes_inscricao = '';
        $this->analistas = [];
        $this->numero_analistas = 0;
    }

    public function buscaCep(BuscaCepAction $buscaCepAction): void
    {
        $cep = $this->laboratorio['endereco']['cep'] ?? '';
        $dados = $buscaCepAction->execute($cep);

        if ($dados) {
            $this->laboratorio['endereco']['endereco'] = $dados['endereco'];
            $this->laboratorio['endereco']['bairro'] = $dados['bairro'];
            $this->laboratorio['endereco']['cidade'] = $dados['cidade'];
            $this->laboratorio['endereco']['uf'] = $dados['uf'];
        } else {
            $this->addError('laboratorio.endereco.cep', 'CEP não encontrado.');
        }
    }

    public function calcularValorEBlocos(): array
    {
        $valorTotal = 0;
        $descricoes = [];

        $blocosSelecionados = $this->getBlocosSelecionadosIds();
        if (! empty($blocosSelecionados)) {
            $blocos = AgendaInterlabValor::whereIn('id', $blocosSelecionados)->get();
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
        $rules = [
            'laboratorio.nome' => ['required', 'string', 'max:191'],
            'laboratorio.responsavel_tecnico' => ['required', 'string', 'max:191'],
            'laboratorio.telefone' => ['nullable', 'string', 'max:15'],
            'laboratorio.email' => ['required', 'email', 'max:191'],
            'laboratorio.endereco.cep' => ['required', 'string'],
            'laboratorio.endereco.endereco' => ['required', 'string'],
            'laboratorio.endereco.bairro' => ['required', 'string'],
            'laboratorio.endereco.uf' => ['required', 'string', 'size:2'],
            'laboratorio.endereco.cidade' => ['required', 'string'],
        ];

        $messages = [
            'laboratorio.nome.required' => 'Preencha o campo laboratório.',
            'laboratorio.nome.max' => 'O campo laboratório deve ter no máximo :max caracteres.',
            'laboratorio.responsavel_tecnico.required' => 'Preencha o campo responsável técnico.',
            'laboratorio.responsavel_tecnico.max' => 'O campo responsável técnico deve ter no máximo :max caracteres.',
            'laboratorio.telefone.*' => 'O telefone informado é inválido.',
            'laboratorio.email.required' => 'O email é obrigatório.',
            'laboratorio.email.email' => 'O email deve ser um endereço de email válido.',
            'laboratorio.endereco.cep.required' => 'Preencha o campo CEP.',
            'laboratorio.endereco.endereco.required' => 'Preencha o campo endereço.',
            'laboratorio.endereco.bairro.required' => 'Preencha o campo bairro.',
            'laboratorio.endereco.cidade.required' => 'Preencha o campo cidade.',
            'laboratorio.endereco.uf.required' => 'Preencha o campo UF.',
            'laboratorio.endereco.uf.size' => 'O campo UF deve ter exatamente 2 caracteres.',
        ];

        if ($this->requer_analistas) {
            $rules['bloco_selecionado'] = ['required', 'integer', 'exists:agendainterlab_valores,id'];
            $messages['bloco_selecionado.required'] = 'Selecione um bloco.';
            $messages['bloco_selecionado.integer'] = 'Seleção de bloco inválida.';
            $messages['bloco_selecionado.exists'] = 'O bloco selecionado é inválido.';
        } else {
            $rules['blocos_selecionados'] = ['required', 'array', 'min:1'];
            $messages['blocos_selecionados.required'] = 'Selecione ao menos um bloco.';
            $messages['blocos_selecionados.min'] = 'Selecione ao menos um bloco.';
        }

        $this->numero_analistas = $this->getNumeroAnalistasSelecionado();
        if ($this->requer_analistas && $this->numero_analistas > 0) {
            for ($i = 0; $i < $this->numero_analistas; $i++) {
                $rules["analistas.{$i}.nome"] = ['required', 'string', 'max:191'];
                $rules["analistas.{$i}.email"] = ['required', 'email', 'max:191'];
                $rules["analistas.{$i}.telefone"] = ['required', 'string', 'max:15'];

                $messages["analistas.{$i}.nome.required"] = 'O nome do analista '.($i + 1).' é obrigatório.';
                $messages["analistas.{$i}.email.required"] = 'O e-mail do analista '.($i + 1).' é obrigatório.';
                $messages["analistas.{$i}.email.email"] = 'O e-mail do analista '.($i + 1).' deve ser um endereço válido.';
                $messages["analistas.{$i}.telefone.required"] = 'O telefone do analista '.($i + 1).' é obrigatório.';
            }
        }

        $this->withValidator(function ($validator) {
            $validator->after(function ($validator) {
                if ($validator->errors()->isNotEmpty()) {
                    $this->dispatch('scroll-to-errors');
                }
            });
        })->validate($rules, $messages);

        if (! empty($this->laboratorio['telefone'])) {
            $this->laboratorio['telefone'] = preg_replace('/\D/', '', $this->laboratorio['telefone']);
        }

        $dadosCalculados = $this->calcularValorEBlocos();
        $valorFinal = $dadosCalculados['valor'];
        $infoFinal = $dadosCalculados['info'];

        $obsExtras = $this->informacoes_inscricao ?? '';
        if (! empty($obsExtras) && ! str_starts_with(trim($obsExtras), 'Blocos:')) {
            $infoFinal .= ' '.$obsExtras;
        }

        $dados = [
            'empresa_id' => $this->empresaId,
            'pessoa_id' => request()->user()->pessoa->id,
            'inscrito_id' => $this->editingId ?: null,
            'laboratorio_id' => $this->editingId
                ? ($this->laboratorio['id'] ?? null)
                : ($this->selecionadoId === 'new' ? null : $this->selecionadoId),
            'laboratorio' => [
                'nome' => $this->laboratorio['nome'],
                'responsavel_tecnico' => $this->laboratorio['responsavel_tecnico'],
                'telefone' => $this->laboratorio['telefone'] ?? null,
                'email' => $this->laboratorio['email'],
                'endereco' => [
                    'cep' => $this->laboratorio['endereco']['cep'],
                    'endereco' => $this->laboratorio['endereco']['endereco'],
                    'complemento' => $this->laboratorio['endereco']['complemento'] ?? null,
                    'bairro' => $this->laboratorio['endereco']['bairro'],
                    'cidade' => $this->laboratorio['endereco']['cidade'],
                    'uf' => $this->laboratorio['endereco']['uf'],
                ],
            ],
            'valor' => $valorFinal,
            'informacoes_inscricao' => $infoFinal,
        ];

        $analistas = $this->requer_analistas && $this->numero_analistas > 0 ? $this->analistas : [];
        $inscrito = app(InscricaoInterlabAction::class)->execute($this->interlab, $dados, $analistas);

        app(NotifyInscricaoInterlabAction::class)->execute($inscrito, $this->interlab);

        app(GerarLancamentoInterlabAction::class)->execute($inscrito, $valorFinal, $this->editingId);
        
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
            return empty($this->bloco_selecionado) ? [] : [(int) $this->bloco_selecionado];
        }

        return collect($this->blocos_selecionados)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();
    }

    private function getNumeroAnalistasSelecionado(): int
    {
        if (! $this->requer_analistas || empty($this->bloco_selecionado)) {
            return count($this->analistas);
        }

        return (int) AgendaInterlabValor::where('id', $this->bloco_selecionado)
            ->value('analistas');
    }
}
