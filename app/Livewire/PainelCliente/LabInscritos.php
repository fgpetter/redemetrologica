<?php

namespace App\Livewire\PainelCliente;

use App\Actions\BuscaCepAction;
use App\Models\AgendaInterlabValor;
use App\Models\Endereco;
use App\Models\InterlabInscrito;
use App\Models\InterlabLaboratorio;
use App\Models\AgendaInterlab; 
use App\Models\Pessoa;
use App\Models\LancamentoFinanceiro;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;
use DB;

class LabInscritos extends Component
{
    public $inscritos = [];
    public $empresaId = null; 
    public $editingId = null;
    public $laboratorio = [];
    public $blocos_selecionados = [];
    public $solicitar_certificado = false;
    public $informacoes_inscricao = '';

    public $interlab;
    public $valores_inscricao;
    public $isVisible = false;

    public function mount()
    {
        $this->interlab = session('interlab');
        $this->valores_inscricao = AgendaInterlabValor::where('agenda_interlab_id', $this->interlab->id)->get();
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
            ->where('pessoa_id', auth()->user()->pessoa->id)
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
    }

    public function cancelEdit()
    {
        $this->editingId = null;
        $this->laboratorio = [];
        $this->blocos_selecionados = [];
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

        if (!empty($this->blocos_selecionados)) {
            $blocos = AgendaInterlabValor::whereIn('id', $this->blocos_selecionados)->get();
            $inscrito = InterlabInscrito::find($this->editingId);
            $isAssociado = $inscrito->empresa->associado ?? false;

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
        $this->validate([
            "laboratorio.nome" => ['required', 'string', 'max:191'],
            "laboratorio.responsavel_tecnico" => ['required', 'string', 'max:191'],
            "laboratorio.telefone" => ['nullable', 'string', 'max:15'],
            "laboratorio.email" => ['required', 'email', 'max:191'],
            "laboratorio.endereco.cep" => ['required', 'string'],
            "laboratorio.endereco.endereco" => ['required', 'string'],
            "laboratorio.endereco.bairro" => ['required', 'string'],
            "laboratorio.endereco.uf" => ['required', 'string', 'size:2'],
            "laboratorio.endereco.cidade" => ['required', 'string'],
            "blocos_selecionados" => ['required', 'array', 'min:1'],
        ], [
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
            'blocos_selecionados.required' => 'Selecione ao menos um bloco.',
            'blocos_selecionados.min' => 'Selecione ao menos um bloco.',
        ]);

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
                $this->adicionaLancamentoFinanceiro($inscrito->agendaInterlab, $inscrito->empresa, $inscrito->laboratorio, $valorFinal);
            }
        });

        $this->editingId = null;

        session()->flash('success', 'Laboratório atualizado com sucesso!');
        $this->loadInscritos();
    }


    private function adicionaLancamentoFinanceiro(AgendaInterlab $agenda_interlab, Pessoa $empresa, InterlabLaboratorio $laboratorio, $valor = null)
    {
        $lancamento = LancamentoFinanceiro::where('pessoa_id', $empresa->id)
            ->where('agenda_interlab_id', $agenda_interlab->id)
            ->first();

        // se a empresa não possui inscritos nesse interlab, cria um novo lançamento
        if (!$lancamento) {
            LancamentoFinanceiro::create([
                'pessoa_id' => $empresa->id,
                'agenda_interlab_id' => $agenda_interlab->id,
                'historico' => 'Inscrição no interlab - ' . $agenda_interlab->interlab->nome,
                'valor' => formataMoeda($valor),
                'centro_custo_id' => '4', // INTERLABORATORIAL
                'plano_conta_id' => '3', // RECEITA PRESTAÇÃO DE SERVIÇOS
                'data_emissao' => now(),
                'status' => 'PROVISIONADO',
                'observacoes' => "Inscrição de {$laboratorio->nome}, com valor de R$ {$valor} \n"
            ]);
        } else { // se a empresa já possui inscritos nesse interlab, atualiza o valor
            $inscricoes_empresa = InterlabInscrito::where('empresa_id', $empresa->id)
                ->where('agenda_interlab_id', $agenda_interlab->id)
                ->whereNotNull('valor')
                ->with('pessoa')
                ->get();

            $observacoes = '';
            foreach ($inscricoes_empresa as $incricao) {
                $nomeLab = $incricao->laboratorio->nome ?? 'Laboratório';
                $data = Carbon::parse($incricao->data_inscricao)->format('d/m/Y H:i');
                $observacoes .= "Inscrição de {$nomeLab}, com valor de R$ {$incricao->valor}, em {$data} \n";
            }

            $lancamento->update([
                'valor' => $inscricoes_empresa->sum('valor'),
                'observacoes' => $observacoes
            ]);
        }
    }


    public function render()
    {
        return view('livewire.painel-cliente.lab-inscritos');
    }
}
