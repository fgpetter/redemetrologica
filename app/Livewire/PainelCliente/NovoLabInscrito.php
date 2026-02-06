<?php

namespace App\Livewire\PainelCliente;

use App\Actions\BuscaCepAction;
use App\Models\AgendaInterlabValor;
use App\Models\Endereco;
use App\Models\InterlabInscrito;
use App\Models\InterlabLaboratorio;
use App\Models\Pessoa;
use App\Models\LancamentoFinanceiro;
use App\Models\AgendaInterlab; 
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Actions\CriarEnviarSenhaAction;
use App\Mail\NovoCadastroInterlabNotification;
use App\Mail\ConfirmacaoInscricaoInterlabNotification;
use Livewire\Component;
use Livewire\Attributes\On;
use DB;

class NovoLabInscrito extends Component
{
    public $empresaId; 
    public $laboratorios_disponiveis = []; 
    public $selecionadoId = null; 
    
    public $laboratorio = []; 
    public $blocos_selecionados = [];
    public $solicitar_certificado = false;
    public $informacoes_inscricao = '';
    
    public $interlab;
    public $valores_inscricao;
    public $isOpen = false;
    public $isVisible = false; 

    public function mount()
    {
        $this->interlab = session('interlab');
        $this->valores_inscricao = AgendaInterlabValor::where('agenda_interlab_id', $this->interlab->id)->get();
        $this->resetForm();
    }

    #[On('empresaSaved')]
    public function setEmpresa($empresa_id)
    {
        $this->empresaId = $empresa_id;
        $this->isVisible = true;
        $this->loadLaboratorios();
        $this->isOpen = true;
    }
    
    #[On('novoLabInscritoSaved')]
    public function reloadLaboratorios()
    {
        $this->loadLaboratorios();
        $this->resetForm();
        $this->selecionadoId = null;
    }

    public function loadLaboratorios()
    {
        if ($this->empresaId) {
            $labsJaInscritos = InterlabInscrito::where('empresa_id', $this->empresaId)
                ->where('agenda_interlab_id', $this->interlab->id)
                ->where('pessoa_id', auth()->user()->pessoa->id)
                ->pluck('laboratorio_id')
                ->toArray();
            
            $this->laboratorios_disponiveis = InterlabLaboratorio::where('empresa_id', $this->empresaId)
                ->whereNotIn('id', $labsJaInscritos)
                ->get();
        }
    }
    
    public function selectLab($labId)
    {
        $this->selecionadoId = $labId;
        $this->resetForm();
        
        if ($labId !== 'new') {
            $labModel = InterlabLaboratorio::with('endereco')->find($labId);
            if ($labModel) {
                $this->laboratorio = $labModel->toArray();
                $this->laboratorio['endereco'] = $labModel->endereco ? $labModel->endereco->toArray() : [];
            }
        }
        $this->dispatch('open-collapse-inner', id: 'collapse-novolab-' . $labId);
    }
    
    private function resetForm() {
        $this->laboratorio = [
            'nome' => '',
            'responsavel_tecnico' => '',
            'telefone' => '',
            'email' => '',
            'endereco' => [
                'cep' => '', 'endereco' => '', 'complemento' => '', 'bairro' => '', 'cidade' => '', 'uf' => ''
            ]
        ];
        $this->blocos_selecionados = [];
        $this->solicitar_certificado = false;
        $this->informacoes_inscricao = '';
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
            
            $isAssociado = false;
            if($this->empresaId) {
                $empresa = Pessoa::find($this->empresaId);
                $isAssociado = $empresa->associado ?? false;
            }

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
        $this->withValidator(function ($validator) {
            $validator->after(function ($validator) {
                if ($validator->errors()->isNotEmpty()) {
                    $this->dispatch('scroll-to-errors');
                }
            });
        })->validate([
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

        $obsExtras = $this->informacoes_inscricao ?? '';
        if (!empty($obsExtras) && !str_starts_with(trim($obsExtras), 'Blocos:')) {
            $infoFinal .= ' ' . $obsExtras;
        }
        
        DB::transaction(function () use ($valorFinal, $infoFinal) {
            $empresaId = $this->empresaId;
            $laboratorioId = null;
            $laboratorio = null;
            
            if ($this->selecionadoId === 'new') {
                 $endereco = Endereco::create([
                    'pessoa_id' => $empresaId,
                    'info' => 'Laboratório: ' . $this->laboratorio['nome'] . ' | Inscrito no PEP: ' . ($this->interlab->nome ?? ''),
                    'cep' => $this->laboratorio['endereco']['cep'],
                    'endereco' => $this->laboratorio['endereco']['endereco'],
                    'complemento' => $this->laboratorio['endereco']['complemento'] ?? null,
                    'bairro' => $this->laboratorio['endereco']['bairro'],
                    'cidade' => $this->laboratorio['endereco']['cidade'],
                    'uf' => $this->laboratorio['endereco']['uf'],
                ]);

                $laboratorio = InterlabLaboratorio::create([
                    'empresa_id' => $empresaId,
                    'endereco_id' => $endereco->id,
                    'nome' => $this->laboratorio['nome'],
    
                ]);
                $laboratorioId = $laboratorio->id;
            } else {
                $laboratorio = InterlabLaboratorio::find($this->selecionadoId);
                $laboratorioId = $laboratorio->id;
                
                $laboratorio->update([
                    'nome' => $this->laboratorio['nome'],

                ]);
                
                if($laboratorio->endereco) {
                     $laboratorio->endereco->update([
                        'cep' => $this->laboratorio['endereco']['cep'],
                        'endereco' => $this->laboratorio['endereco']['endereco'],
                        'complemento' => $this->laboratorio['endereco']['complemento'] ?? null,
                        'bairro' => $this->laboratorio['endereco']['bairro'],
                        'cidade' => $this->laboratorio['endereco']['cidade'],
                        'uf' => $this->laboratorio['endereco']['uf'],
                        'info' => 'Laboratório: ' . $this->laboratorio['nome'] . ' | Inscrito no PEP: ' . ($this->interlab->nome ?? ''),
                     ]);
                }
            }
            
             $senha = null;
            if (!empty($this->interlab->interlab->tag)) {
                $senha = $this->interlab->interlab->tag . rand(111, 999);
                while (
                    InterlabInscrito::where('tag_senha', $senha)
                        ->where('agenda_interlab_id', $this->interlab->id)
                        ->exists()
                ) {
                    $senha = $this->interlab->interlab->tag . rand(111, 999);
                }
            }

            $inscrito = InterlabInscrito::create([
                'pessoa_id' => auth()->user()->pessoa->id,
                'empresa_id' => $empresaId,
                'laboratorio_id' => $laboratorioId,
                'agenda_interlab_id' => $this->interlab->id,
                'data_inscricao' => now(),
                'valor' => $valorFinal,
                'informacoes_inscricao' => $infoFinal,
                'tag_senha' => $senha,
                'responsavel_tecnico' => $this->laboratorio['responsavel_tecnico'], 
                'telefone' => $this->laboratorio['telefone'], 
                'email' => $this->laboratorio['email'], 
            ]);

            Mail::to('interlab@redemetrologica.com.br')
                ->cc(['tecnico@redemetrologica.com.br', 'sistema@redemetrologica.com.br'])
                ->send(new NovoCadastroInterlabNotification($inscrito, $this->interlab));

            Mail::to($inscrito->pessoa->email)
                ->cc('sistema@redemetrologica.com.br')
                ->send(new ConfirmacaoInscricaoInterlabNotification($inscrito, $this->interlab));

            if ($this->interlab->status === 'CONFIRMADO' && !empty($this->interlab->interlab->tag)) {
                app(CriarEnviarSenhaAction::class)->execute($inscrito, 1);
            }
            
            app(\App\Actions\Financeiro\GerarLancamentoInterlabAction::class)->execute($inscrito, $valorFinal);
        });
        
        $this->selecionadoId = null;
        $this->dispatch('novoLabInscritoSaved');
        $this->dispatch('close-accordion', id: 'accordion-novo-lab');
        
        session()->flash('success', 'Inscrição realizada com sucesso!');
    }
    
    

    public function render()
    {
        return view('livewire.painel-cliente.novo-lab-inscrito');
    }
}
