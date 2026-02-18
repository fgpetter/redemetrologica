<?php

namespace App\Livewire\PainelCliente;

use App\Models\Pessoa;
use Livewire\Component;
use App\Models\Endereco;
use Livewire\Attributes\On;
use App\Actions\BuscaCepAction;
use App\Models\InterlabAnalista;
use App\Models\InterlabInscrito;
use Livewire\Attributes\Computed;
use App\Models\AgendaInterlabValor;
use App\Models\InterlabLaboratorio;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Actions\CriarEnviarSenhaAction;
use App\Mail\NovoCadastroInterlabNotification;
use App\Mail\ConfirmacaoInscricaoInterlabNotification;
use App\Actions\Financeiro\GerarLancamentoInterlabAction;

class NovoLabInscrito extends Component
{
    public $empresaId; 
    public $laboratorios_disponiveis = []; 
    public $selecionadoId = null; 
    
    public $laboratorio = []; 
    public $blocos_selecionados = [];
    public $bloco_selecionado = null;
    public $solicitar_certificado = false;
    public $informacoes_inscricao = '';
    
    public $analistas = [];
    public $numero_analistas = 0;
    public $requer_analistas = false;
    
    public $interlab;
    public $valores_inscricao;
    public $isOpen = false;
    public $isVisible = false; 

    public function mount()
    {
        $this->interlab = session('interlab');
        $this->valores_inscricao = AgendaInterlabValor::where('agenda_interlab_id', $this->interlab->id)->get();
        $this->requer_analistas = ($this->interlab->interlab->avaliacao ?? null) === 'ANALISTA';
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
                ->where('pessoa_id', request()->user()->pessoa->id)
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
        $this->bloco_selecionado = null;
        $this->solicitar_certificado = false;
        $this->informacoes_inscricao = '';
        $this->analistas = [];
        $this->numero_analistas = 0;
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

        $this->withValidator(function ($validator) {
            $validator->after(function ($validator) {
                if ($validator->errors()->isNotEmpty()) {
                    $this->dispatch('scroll-to-errors');
                }
            });
        })->validate($rules, $messages);
        
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
                'pessoa_id' => request()->user()->pessoa->id,
                'empresa_id' => $empresaId,
                'laboratorio_id' => $laboratorioId,
                'agenda_interlab_id' => $this->interlab->id,
                'data_inscricao' => now(),
                'valor' => $valorFinal,
                'informacoes_inscricao' => $infoFinal,
                'tag_senha' => $senha,
                'responsavel_tecnico' => $this->laboratorio['responsavel_tecnico'], 
                'telefone' => $this->laboratorio['telefone'] ?? null, 
                'email' => $this->laboratorio['email'], 
            ]);

            // Salva analistas se necessário
            if ($this->requer_analistas && $this->numero_analistas > 0) {
                foreach ($this->analistas as $analistaData) {
                    InterlabAnalista::create([
                        'interlab_inscrito_id' => $inscrito->id,
                        'nome' => $analistaData['nome'],
                        'email' => $analistaData['email'],
                        'telefone' => preg_replace('/\D/', '', $analistaData['telefone']),
                    ]);
                }
            }

            Mail::to('interlab@redemetrologica.com.br')
                ->cc(['tecnico@redemetrologica.com.br', 'sistema@redemetrologica.com.br'])
                ->send(new NovoCadastroInterlabNotification($inscrito, $this->interlab));

            Mail::to($inscrito->pessoa->email)
                ->cc('sistema@redemetrologica.com.br')
                ->send(new ConfirmacaoInscricaoInterlabNotification($inscrito, $this->interlab));

            if ($this->interlab->status === 'CONFIRMADO' && !empty($this->interlab->interlab->tag)) {
                app(CriarEnviarSenhaAction::class)->execute($inscrito, 1);
            }
            
            app(GerarLancamentoInterlabAction::class)->execute($inscrito, $valorFinal);
        });
        
        $this->selecionadoId = null;
        $this->dispatch('novoLabInscritoSaved');
        $this->dispatch('close-accordion', id: 'accordion-novo-lab');
        
        session()->flash('success', 'Inscrição realizada com sucesso!');
    }
    
    

    #[Computed]
    public function isAssociado()
    {
        if ($this->empresaId) {
            $empresa = Pessoa::find($this->empresaId);
            return $empresa->associado ?? false;
        }
        return false;
    }

    public function render()
    {
        return view('livewire.painel-cliente.novo-lab-inscrito');
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
            return 0;
        }

        return (int) AgendaInterlabValor::where('id', $this->bloco_selecionado)
            ->value('analistas');
    }
}
