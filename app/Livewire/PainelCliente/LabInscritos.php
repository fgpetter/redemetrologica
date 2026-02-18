<?php

namespace App\Livewire\PainelCliente;

use App\Actions\BuscaCepAction;
use App\Models\AgendaInterlabValor;
use App\Models\InterlabAnalista;
use App\Models\InterlabInscrito;
use App\Models\Pessoa;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;

class LabInscritos extends Component
{
    public $inscritos = [];
    public $empresaId = null; 
    public $editingId = null;
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

    public function mount()
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
    }

    #[On('empresaSaved')]
    public function setEmpresa($empresa_id)
    {
        $this->empresaId = $empresa_id;
        $this->isVisible = true;
        $this->loadInscritos();
    }
    
    #[On('novoLabInscritoSaved')]
    public function reloadInscritos()
    {
        $this->loadInscritos();
    }
    
    public function loadInscritos()
    {
        if (!$this->empresaId) {
            $this->inscritos = [];
            return;
        }
        
        $this->inscritos = InterlabInscrito::with('laboratorio.endereco')
            ->where('pessoa_id', request()->user()->pessoa->id)
            ->where('empresa_id', $this->empresaId)
            ->where('agenda_interlab_id', $this->interlab->id)
            ->get();
    }

    public function edit($inscritoId)
    {
        $inscrito = InterlabInscrito::with(['laboratorio.endereco'])->findOrFail($inscritoId);
        $this->editingId = $inscritoId;

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
        if (!empty($infoInscricao)) {
            if (preg_match('/^Blocos:.*?\.(.*)$/', $infoInscricao, $matches)) {
                $this->informacoes_inscricao = trim($matches[1]);
            } elseif (!str_starts_with(trim($infoInscricao), 'Blocos:')) {
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

    public function cancelEdit()
    {
        $this->editingId = null;
        $this->laboratorio = [];
        $this->blocos_selecionados = [];
        $this->bloco_selecionado = null;
    }

    public function buscaCep(BuscaCepAction $buscaCepAction)
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

    public function calcularValorEBlocos()
    {
        $valorTotal = 0;
        $descricoes = [];

        $blocosSelecionados = $this->getBlocosSelecionadosIds();
        if (!empty($blocosSelecionados)) {
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

        $info = !empty($descricoes) ? 'Blocos: ' . implode(', ', $descricoes) . '.' : '';
        if ($this->solicitar_certificado) {
            $info .= ' | Certificado de Desempenho solicitado.';
        }

        return ['valor' => $valorTotal, 'info' => $info];
    }

    public function salvar()
    {
        dd('teste');
        $rules = [
            "laboratorio.nome" => ['required', 'string', 'max:191'],
            "laboratorio.responsavel_tecnico" => ['required', 'string', 'max:191'],
            "laboratorio.telefone" => ['nullable', 'string', 'max:15'],
            "laboratorio.email" => ['required', 'email', 'max:191'],
            "laboratorio.endereco.cep" => ['required', 'string'],
            "laboratorio.endereco.endereco" => ['required', 'string'],
            "laboratorio.endereco.bairro" => ['required', 'string'],
            "laboratorio.endereco.uf" => ['required', 'string', 'size:2'],
            "laboratorio.endereco.cidade" => ['required', 'string'],
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

                $messages["analistas.{$i}.nome.required"] = "O nome do analista " . ($i + 1) . " é obrigatório.";
                $messages["analistas.{$i}.email.required"] = "O e-mail do analista " . ($i + 1) . " é obrigatório.";
                $messages["analistas.{$i}.email.email"] = "O e-mail do analista " . ($i + 1) . " deve ser um endereço válido.";
                $messages["analistas.{$i}.telefone.required"] = "O telefone do analista " . ($i + 1) . " é obrigatório.";
            }
        }

        $this->validate($rules, $messages);

        if (!empty($this->laboratorio['telefone'])) {
            $this->laboratorio['telefone'] = preg_replace('/\D/', '', $this->laboratorio['telefone']);
        }

        $dadosCalculados = $this->calcularValorEBlocos();
        $valorFinal = $dadosCalculados['valor'];
        $infoFinal = $dadosCalculados['info'];

        $obsExtras = trim($this->informacoes_inscricao ?? '');
        if (!empty($obsExtras) && !str_starts_with($obsExtras, 'Blocos:')) {
            $infoFinal = trim($infoFinal . ' ' . $obsExtras);
        }

        DB::transaction(function () use ($valorFinal, $infoFinal) {
            $inscrito = InterlabInscrito::find($this->editingId);
            $laboratorio = $inscrito->laboratorio;
            $endereco = $laboratorio->endereco;

            $endereco->update([
                'cep' => $this->laboratorio['endereco']['cep'],
                'endereco' => $this->laboratorio['endereco']['endereco'],
                'complemento' => $this->laboratorio['endereco']['complemento'] ?? null,
                'bairro' => $this->laboratorio['endereco']['bairro'],
                'cidade' => $this->laboratorio['endereco']['cidade'],
                'uf' => $this->laboratorio['endereco']['uf'],
                'info' => 'Laboratório: ' . $this->laboratorio['nome'] . ' | Inscrito no PEP: ' . ($this->interlab->nome ?? ''),
            ]);

            $laboratorio->update([
                'nome' => $this->laboratorio['nome'],
            ]);

            $inscrito->update([
                'valor' => $valorFinal,
                'informacoes_inscricao' => $infoFinal,
                'responsavel_tecnico' => $this->laboratorio['responsavel_tecnico'],
                'telefone' => $this->laboratorio['telefone'],
                'email' => $this->laboratorio['email'],
            ]);

            
            if ($valorFinal > 0) {
                app(\App\Actions\Financeiro\GerarLancamentoInterlabAction::class)->execute($inscrito, $valorFinal);
            }
        });

        $this->editingId = null;

        session()->flash('success', 'Laboratório atualizado com sucesso!');
        $this->loadInscritos();
    }


    #[Computed]
    public function isAssociado()
    {
        if($this->empresaId) {
            $empresa = Pessoa::find($this->empresaId);
            return $empresa->associado ?? false;
        } elseif ($this->editingId) {
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
