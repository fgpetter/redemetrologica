<?php

namespace App\Livewire\PainelCliente;

use App\Models\Pessoa;
use Livewire\Component;
use App\Models\Endereco;
use App\Models\AgendaInterlab;
use App\Models\InterlabInscrito;
use Illuminate\Support\Facades\DB;
use App\Models\InterlabLaboratorio;

class ConfirmInscricaoInterlab extends Component
{
    // dados para edições
    public $showSalvarEmpresa = false;
    public $showInscreveLab = false;
    public $showInscreveNOVOLab = false;

    public $empresaEditadaId = null;
    public $editandoLaboratorio = false;
    public $novaInscricaoEmpresaId = null;
    public $inscritoId;
    public $inscritoEditadoId = null;
    public $laboratorioId;
    public $laboratorioEditadoId = null;

    public $pessoaId_usuario; //id da pessoa do usuario
    public $interlab; //vem da session com dados da agenda interlab
    public $BuscaCnpj; // cnpj para busca de pessoa
    public $empresa; //vem do ProcuraCnpj
    public $pessoa; // empresas que o usuario ja tenha inscrito
    public $inscritos; //interlabs que usuario ja esta inscrito

    // dados para update da empresa
    public $nome_razao;
    public $cpf_cnpj;
    public $emailNF;
    public $telefone;

    // Dados da inscrição interlab
    public $laboratorio;
    public $responsavel_tecnico;
    public $lab_telefone;
    public $lab_email;
    public $informacoes_inscricao;
    public $valor;

    // Dados do endereço do Laboratorio 
    public $cep;
    public $endereco;
    public $complemento;
    public $bairro;
    public $cidade;
    public $uf;

    public function mount()
    {
        $this->pessoaId_usuario = auth()->user()->pessoa->id;

        /** @var AgendaInterlab */
        $this->interlab = session('interlab') ?? null;

        /** @var \App\Models\InterlabInscrito */
        $this->inscritos = InterlabInscrito::with('laboratorio')
            ->where('pessoa_id',  auth()->user()->pessoa->id)
            ->where('agenda_interlab_id', $this->interlab->id)
            ->get() ?? null;

        $empresaIds = $this->inscritos->pluck('empresa_id')->unique();
        /** @var Pessoa */
        $this->pessoa = Pessoa::whereIn('id', $empresaIds)
            ->with('interlabs')
            ->get();

        $this->reset([
            'editandoLaboratorio',
            'inscritoId',
            'laboratorioId'
        ]);
    }

    public function render()
    {
        return view('livewire.painel-cliente.confirm-inscricao-interlab');
    }

    //Medoto procura cnpj
    public function ProcuraCnpj()
    {
        $this->validate([
            'BuscaCnpj' => ['required', 'cnpj'],
        ], [
            'BuscaCnpj.cnpj' => 'O dado enviado não é um CNPJ válido',
        ]);

        $cnpjLimpo = preg_replace('/[^0-9]/', '', $this->BuscaCnpj);

        $empresa = Pessoa::where('cpf_cnpj', $cnpjLimpo)
            ->where('tipo_pessoa', 'PJ')
            ->first();

        if ($empresa) {
            $this->empresa       = $empresa;
            $this->nome_razao    = $empresa->nome_razao;
            $this->cpf_cnpj      = $empresa->cpf_cnpj;
            $this->telefone      = $empresa->telefone;
            $this->emailNF       = $empresa->emailNF;

            $this->showSalvarEmpresa = true; // Mostra formulário de empresa
            $this->showInscreveLab = false;

        } else {
            $this->empresa = null;
            $this->cpf_cnpj = $cnpjLimpo;

            $this->showSalvarEmpresa = true; // Mostra formulário de empresa
            $this->showInscreveLab = false;
        }
        $this->BuscaCnpj = null;
    }

    //metodo para salvar empresa
    public function salvarEmpresa()
    {
        $this->validate([
            'nome_razao' => 'required',
            'cpf_cnpj'   => 'required', //completar dps
            'emailNF'   => 'required',
        ]);

        if ($this->empresa) {
            $this->empresa->update([
                'nome_razao'     => $this->nome_razao,
                'cpf_cnpj'       => $this->cpf_cnpj,
                'telefone'       => $this->telefone,
                'emailNF'          => $this->emailNF,
            ]);
            session()->flash('message', 'Empresa atualizada com sucesso!');

            // Reset dos campos de edição
            $this->reset(['nome_razao', 'cpf_cnpj', 'telefone', 'emailNF']);
            
        } else {
            $this->empresa = Pessoa::create([
                'nome_razao'     => $this->nome_razao,
                'cpf_cnpj'       => $this->cpf_cnpj,
                'tipo_pessoa'    => 'PJ',
                'telefone'       => $this->telefone,
                'emailNF'          => $this->emailNF,
            ]);
            session()->flash('message', 'Empresa cadastrada com sucesso!');
        }
        $this->showSalvarEmpresa = false;
        //   atualizando  os dados necessários:
        $this->inscritos = InterlabInscrito::with('laboratorio')
        ->where('pessoa_id', auth()->user()->pessoa->id)
        ->where('agenda_interlab_id', $this->interlab->id)
        ->get();
        
        $empresaIds = $this->inscritos->pluck('empresa_id')->unique();
        $this->pessoa = Pessoa::whereIn('id', $empresaIds)
        ->with('interlabs')
        ->get();
        $this->empresaEditadaId = null;
        if (in_array($this->empresa->id, $empresaIds->toArray())) {
            $this->showInscreveLab = false;
        } else {
            $this->showInscreveLab = true; // Mostra formulário de laboratório
        }
    }
    
    //metodo para editar empresas (preenche os campos)
    public function editEmpresa($empresaId)
    {
        $this->empresaEditadaId = $empresaId;
        $this->empresa = Pessoa::find($empresaId);
        $this->nome_razao = $this->empresa->nome_razao;
        $this->cpf_cnpj = $this->empresa->cpf_cnpj;
        $this->telefone = $this->empresa->telefone;
        $this->emailNF = $this->empresa->emailNF;
    }

    public function rules(): array
    {
        return [
            "laboratorio" => ['required', 'string', 'max:191'],
            "responsavel_tecnico" => ['required', 'string', 'max:191'],
            "lab_telefone" => ['nullable', 'string', 'min:10', 'max:11'],
            "lab_email" => ['nullable', 'email', 'max:191'],
            "informacoes_inscricao" => ['nullable', 'string'],
            "cep" => ['required', 'string'],
            "endereco" => ['required', 'string'],
            "complemento" => ['nullable', 'string'],
            "bairro" => ['nullable', 'string'],
            "cidade" => ['nullable', 'string'],
            "uf" => ['required', 'string'],
            "valor" => ['nullable', 'string'],
        ];
    }
    public function messages(): array
    {
        return [
            'laboratorio.required' => 'Preencha o campo laboratório',
            'laboratorio.max' => 'O campo laboratório deve ter no máximo :max caracteres',
            'responsavel_tecnico.required' => 'Preencha o campo responsável técnico',
            'responsavel_tecnico.max' => 'O campo responsável técnico deve ter no máximo :max caracteres',
            'lab_telefone.*' => 'O telefone informado é inválido',
            'lab_email.*' => 'O email informado é inválido',
            'cep.required' => 'Preencha o campo CEP',
            'endereco.required' => 'Preencha o campo endereço',
            'uf.required' => 'Preencha o campo UF',
            'valor.string' => 'O valor digitado é inválido',
        ];
    }

    // Metodo  salva interlab-inscritos, interlab-laboratorio e endereco
    public function InscreveLab()
    {
        $validated = $this->validate();

        try {
            DB::transaction(function () use ($validated) {
                if ($this->laboratorioEditadoId) {

                    $laboratorio = InterlabLaboratorio::findOrFail($this->laboratorioEditadoId);
                    $endereco = Endereco::findOrFail($laboratorio->endereco_id);

                    // Atualiza endereço
                    $endereco->update([
                        'cep' => $validated['cep'],
                        'endereco' => $validated['endereco'],
                        'complemento' => $validated['complemento'],
                        'bairro' => $validated['bairro'],
                        'cidade' => $validated['cidade'],
                        'uf' => $validated['uf'],
                    ]);

                    // Atualiza laboratório
                    $laboratorio->update([
                        'nome' => $validated['laboratorio'],
                        'responsavel_tecnico' => $validated['responsavel_tecnico'],
                        'telefone' => $validated['lab_telefone'],
                        'email' => $validated['lab_email'],
                    ]);

                    // Atualiza inscrito
                    InterlabInscrito::where('id', $this->inscritoId)->update([
                        'valor' => $validated['valor'] ?? null,
                        'informacoes_inscricao' => $validated['informacoes_inscricao'],
                    ]);
                } else {
                    // NOVO CADASTRO
                    $empresaId = $this->novaInscricaoEmpresaId ?? $this->empresa->id;

                    $endereco = Endereco::create([
                        'pessoa_id' => $empresaId,
                        'info' => 'Laboratório: ' . $validated['laboratorio'],
                        'cep' => $validated['cep'],
                        'endereco' => $validated['endereco'],
                        'complemento' => $validated['complemento'],
                        'bairro' => $validated['bairro'],
                        'cidade' => $validated['cidade'],
                        'uf' => $validated['uf'],
                    ]);

                    $laboratorio = InterlabLaboratorio::create([
                        'empresa_id' => $empresaId,
                        'endereco_id' => $endereco->id,
                        'nome' => $validated['laboratorio'],
                        'responsavel_tecnico' => $validated['responsavel_tecnico'],
                        'telefone' => $validated['lab_telefone'],
                        'email' => $validated['lab_email'],
                    ]);

                    InterlabInscrito::create([
                        'pessoa_id' => $this->pessoaId_usuario,
                        'empresa_id' => $empresaId,
                        'laboratorio_id' => $laboratorio->id,
                        'agenda_interlab_id' => $this->interlab->id,
                        'data_inscricao' => now(),
                        'valor' => $validated['valor'] ?? null,
                        'informacoes_inscricao' => $validated['informacoes_inscricao'],
                    ]);
                }
            });

            session()->flash('success', $this->laboratorioEditadoId
                ? 'Laboratório atualizado com sucesso!'
                : 'Laboratório cadastrado com sucesso!');

            $this->reset([
                'novaInscricaoEmpresaId',
                'laboratorioEditadoId',
                'inscritoEditadoId'
            ]);

            $this->cancelEdit();
        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao processar: ' . $e->getMessage());
        }
        $this->showInscreveLab = false; // Esconde formulários
        $this->showSalvarEmpresa = false;
        $this->mount();

        // FALTA enviar e-mails
        // inserir financeiro
        // buscar CEP
        // limpar sessão
    }

    //Metodo para editar inscricoes
    public function editLaboratorio($inscritoId)
    {
        $this->inscritoEditadoId = $inscritoId;
        $inscrito = InterlabInscrito::with(['laboratorio.endereco'])->findOrFail($inscritoId);

        // Preenche os campos do formulário
        $this->laboratorio = $inscrito->laboratorio->nome;
        $this->responsavel_tecnico = $inscrito->laboratorio->responsavel_tecnico;
        $this->lab_telefone = $inscrito->laboratorio->telefone;
        $this->lab_email = $inscrito->laboratorio->email;
        $this->informacoes_inscricao = $inscrito->informacoes_inscricao;
        $this->valor = $inscrito->valor;

        // Endereço
        $this->cep = $inscrito->laboratorio->endereco->cep;
        $this->endereco = $inscrito->laboratorio->endereco->endereco;
        $this->complemento = $inscrito->laboratorio->endereco->complemento;
        $this->bairro = $inscrito->laboratorio->endereco->bairro;
        $this->cidade = $inscrito->laboratorio->endereco->cidade;
        $this->uf = $inscrito->laboratorio->endereco->uf;

        $this->laboratorioEditadoId = $inscrito->laboratorio->id;
    }
    // novo laboratorio praa empresa ja inscrita
    public function novoLaboratorio($empresaId)
    {
        $this->novaInscricaoEmpresaId = $empresaId;
        $this->reset(['laboratorioEditadoId', 'inscritoEditadoId']);
        $this->cancelEdit(); 
    }
    // Cancelar edições
    public function cancelEdit()
    {
        $this->reset([
            'laboratorioEditadoId',
            'inscritoEditadoId',
            'laboratorio',
            'responsavel_tecnico',
            'lab_telefone',
            'lab_email',
            'informacoes_inscricao',
            'valor',
            'cep',
            'endereco',
            'complemento',
            'bairro',
            'cidade',
            'uf'
        ]);
    }
}
