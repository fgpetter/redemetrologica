<?php

namespace App\Livewire\PainelCliente;

use App\Models\Pessoa;
use Livewire\Component;
use App\Models\Endereco;
use App\Models\AgendaInterlab;
use App\Models\InterlabInscrito;
use Illuminate\Support\Facades\DB;
use App\Models\InterlabLaboratorio;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\NovoCadastroInterlabNotification;
use App\Mail\ConfirmacaoInscricaoInterlabNotification;

class ConfirmInscricaoInterlab extends Component
{
    // dados para edições
    public $showSalvarEmpresa = false;
    public $showInscreveLab = false;
    public $showInscreveNOVOLab = false;

    public $empresaEditadaId = null;
    public $novaInscricaoEmpresaId = null;
    public $inscritoId;
    public $inscritoEditadoId = null;
    public $laboratorioId;
    public $laboratorioEditadoId = null;

    public $pessoaId_usuario; //id da pessoa do usuario
    public $interlab; //vem da session com dados da agenda interlab
    public $BuscaCnpj; // cnpj para busca de pessoa
    public $empresa; //vem do ProcuraCnpj
    public $empresas; // empresas que o usuario ja tenha inscrito
    public $inscritos; //interlabs que usuario ja esta inscrito

    // dados para update da empresa
    public $nome_razao;
    public $cpf_cnpj;
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

    // Dados do endereço de cobrança
    public $cobranca_email;
    public $cobranca_cep;
    public $cobranca_endereco;
    public $cobranca_complemento;
    public $cobranca_bairro;
    public $cobranca_cidade;
    public $cobranca_uf;

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
        $this->empresas = Pessoa::whereIn('id', $empresaIds)
            ->with(['interlabs', 'enderecos' => function ($query) {
                $query->where('cobranca', 1);
            }])
            ->get();

        $this->reset([ 'inscritoId', 'laboratorioId' ]);
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
            'BuscaCnpj.cnpj' => 'O dado enviado não é um CNPJ válido.',
            'BuscaCnpj.required' => 'Digite um CNPJ para cadastro.',
        ]);

        $cnpjLimpo = preg_replace('/[^0-9]/', '', $this->BuscaCnpj);

        $empresa = Pessoa::with('enderecoCobranca') // Adiciona o relacionamento enderecoCobranca
            ->where('cpf_cnpj', $cnpjLimpo)
            ->where('tipo_pessoa', 'PJ')
            ->first();
        if ($empresa) {
            $this->empresa       = $empresa;
            $this->nome_razao    = $empresa->nome_razao;
            $this->cpf_cnpj      = $empresa->cpf_cnpj;
            $this->telefone      = $empresa->telefone;

            // Preenche os dados do endereço de cobrança, se existir
            if ($empresa->enderecoCobranca) {
                $this->cobranca_cep = $empresa->enderecoCobranca->cep;
                $this->cobranca_endereco = $empresa->enderecoCobranca->endereco;
                $this->cobranca_complemento = $empresa->enderecoCobranca->complemento;
                $this->cobranca_bairro = $empresa->enderecoCobranca->bairro;
                $this->cobranca_cidade = $empresa->enderecoCobranca->cidade;
                $this->cobranca_uf = $empresa->enderecoCobranca->uf;
                $this->cobranca_email = $empresa->enderecoCobranca->email;
            }

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
        $this->validate(
            [
                'nome_razao' => ['required', 'string', 'max:191'],
                'cpf_cnpj' => ['required', 'cpf_ou_cnpj', 'max:191'],
                'cobranca_email' => ['required', 'email', 'max:191'],
                'cobranca_cep' => ['required', 'string'],
                'cobranca_endereco' => ['required', 'string'],
                "cobranca_email" => ['required', 'email', 'max:191'],
                'cobranca_bairro' => ['required', 'string'],
                'cobranca_cidade' => ['required', 'string'],
                'cobranca_uf' => ['required', 'string', 'size:2'],
            ],[
                'nome_razao.required' => 'Preencha o campo nome/razão social.',
                'nome_razao.max' => 'O campo nome/razão social deve ter no máximo :max caracteres.',
                'cpf_cnpj.required' => 'Preencha o campo CPF/CNPJ.',
                'cobranca_cep.required' => 'Preencha o campo CEP de cobrança.',
                'cobranca_endereco.required' => 'Preencha o campo endereço de cobrança.',
                'cobranca_bairro.required' => 'Preencha o campo bairro de cobrança.',
                'cobranca_email.required' => 'O email de cobrança é obrigatório.',
                'cobranca_email.email' => 'O email de cobrança deve ser um endereço de email válido.',
                'cobranca_cidade.required' => 'Preencha o campo cidade de cobrança.',
                'cobranca_uf.required' => 'Preencha o campo UF de cobrança.',
                'cobranca_uf.size' => 'O campo UF de cobrança deve ter exatamente 2 caracteres.',
            ]
        );

        if ($this->empresa) {
            $this->empresa->update([
                'nome_razao'     => $this->nome_razao,
                'cpf_cnpj'       => $this->cpf_cnpj,
                'telefone'       => $this->telefone,
            ]);
            
        } else {
            $this->empresa = Pessoa::create([
                'nome_razao'     => $this->nome_razao,
                'cpf_cnpj'       => $this->cpf_cnpj,
                'tipo_pessoa'    => 'PJ',
                'telefone'       => $this->telefone,
            ]);
        }

            $endereco = $this->empresa->enderecoCobranca()->updateOrCreate(
            ['pessoa_id' => $this->empresa->id],
            [
                'info' => 'Cobrança',
                'cep' => $this->cobranca_cep,
                'endereco' => $this->cobranca_endereco,
                'complemento' => $this->cobranca_complemento,
                'bairro' => $this->cobranca_bairro,
                'cidade' => $this->cobranca_cidade,
                'uf' => $this->cobranca_uf,
                'email' => $this->cobranca_email,
                'cobranca' => 1,
            ]);
            $this->empresa->update([
                'end_cobranca' => $endereco->id,
            ]);
        

        $this->showSalvarEmpresa = false;

        //   atualizando  os dados necessários:
        $this->inscritos = InterlabInscrito::with('laboratorio')
            ->where('pessoa_id', auth()->user()->pessoa->id)
            ->where('agenda_interlab_id', $this->interlab->id)
            ->get();

        $empresaIds = $this->inscritos->pluck('empresa_id')->unique();

        $this->empresas = Pessoa::whereIn('id', $empresaIds)
            ->with('interlabs')
            ->get();

        $this->empresaEditadaId = null;

        if (in_array($this->empresa->id, $empresaIds->toArray())) {
            $this->showInscreveLab = false;
        } else {
            $this->showInscreveLab = true; // Mostra formulário de laboratório
        }
        // Reset dos campos de edição
        $this->reset([
            'nome_razao',
            'cpf_cnpj',
            'telefone',
            'cobranca_email',
            'cobranca_cep',
            'cobranca_endereco',
            'cobranca_complemento',
            'cobranca_bairro',
            'cobranca_cidade',
            'cobranca_uf',
        ]);
    }

    //metodo para editar empresas (preenche os campos)
    public function editEmpresa($empresaId)
    {
        $this->empresaEditadaId = $empresaId;
        $this->empresa = Pessoa::with('enderecoCobranca')->find($empresaId); // Carrega o relacionamento enderecoCobranca

        $this->nome_razao = $this->empresa->nome_razao;
        $this->cpf_cnpj = $this->empresa->cpf_cnpj;
        $this->telefone = $this->empresa->telefone;

        // Preenche os campos do endereço de cobrança, se existir
        if ($this->empresa->enderecoCobranca) {
            $this->cobranca_cep = $this->empresa->enderecoCobranca->cep;
            $this->cobranca_endereco = $this->empresa->enderecoCobranca->endereco;
            $this->cobranca_complemento = $this->empresa->enderecoCobranca->complemento;
            $this->cobranca_bairro = $this->empresa->enderecoCobranca->bairro;
            $this->cobranca_cidade = $this->empresa->enderecoCobranca->cidade;
            $this->cobranca_uf = $this->empresa->enderecoCobranca->uf;
            $this->cobranca_email = $this->empresa->enderecoCobranca->email;
        } else {
            // Reseta os campos caso não exista endereço de cobrança
            $this->reset([
                'cobranca_cep',
                'cobranca_endereco',
                'cobranca_complemento',
                'cobranca_bairro',
                'cobranca_cidade',
                'cobranca_uf',
                'cobranca_email',
            ]);
        }
    }

    public function rules(): array
    {
        return [
            "laboratorio" => ['required', 'string', 'max:191'],
            "responsavel_tecnico" => ['required', 'string', 'max:191'],
            "lab_telefone" => ['nullable', 'string', 'min:10', 'max:11'],
            "lab_email" => ['required', 'email', 'max:191'],
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
            'lab_email.required' => 'O email  é obrigatório.',
            'lab_email.email' => 'O email deve ser um endereço de email válido.',
            'cep.required' => 'Preencha o campo CEP',
            'endereco.required' => 'Preencha o campo endereço',
            'uf.required' => 'Preencha o campo UF',
            'valor.string' => 'O valor digitado é inválido',
        ];
    }

    // Metodo  salva interlab-inscritos, interlab-laboratorio e endereco
    public function InscreveLab()
    {
        // sanitiza telefone
        if (!empty($this->lab_telefone)) {
            $this->lab_telefone = preg_replace('/\D/', '', $this->lab_telefone);
        }
        $validated = $this->validate();

        try {
            $inscrito = DB::transaction(function () use ($validated) {
                
                if ($this->laboratorioEditadoId) {
                    // Obtém o ID da empresa associada ao laboratório
                    $laboratorio = InterlabLaboratorio::findOrFail($this->laboratorioEditadoId);
                    $empresaId = $laboratorio->empresa_id;

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

                    // Obtém o endereço de cobrança
                    $enderecoCobranca = Endereco::where('pessoa_id', $empresaId)
                        ->where('cobranca', 1)
                        ->orderBy('updated_at', 'desc')
                        ->first();
                        

                    // Atualiza inscrito
                    InterlabInscrito::where('id', $this->inscritoEditadoId)->update([
                        'valor' => $validated['valor'] ?? null,
                        'informacoes_inscricao' => $validated['informacoes_inscricao'],
                        'end_cobranca' => $enderecoCobranca->id ?? null,
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

                    $enderecoCobranca = Endereco::where('pessoa_id', $empresaId)
                        ->where('cobranca', 1)
                        ->orderBy('updated_at', 'desc')
                        ->first();

                    $inscrito =InterlabInscrito::create([
                        'pessoa_id' => $this->pessoaId_usuario,
                        'empresa_id' => $empresaId,
                        'laboratorio_id' => $laboratorio->id,
                        'agenda_interlab_id' => $this->interlab->id,
                        'data_inscricao' => now(),
                        'valor' => $validated['valor'] ?? null,
                        'informacoes_inscricao' => $validated['informacoes_inscricao'],
                        'end_cobranca' => $enderecoCobranca->id ?? null,
                    ]);

                    // Mail::to('interlab@redemetrologica.com.br')
                    //     ->cc('bonus@redemetrologica.com.br')
                    //     ->cc('sistema@redemetrologica.com.br')
                    //     ->send(new NovoCadastroInterlabNotification($inscrito, $this->interlab));

                    // Mail::to($inscrito->pessoa->email)
                    //     ->cc('sistema@redemetrologica.com.br')
                    //     ->send(new ConfirmacaoInscricaoInterlabNotification($inscrito, $this->interlab));
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

            if (isset($request->valor) && $request->valor > 0) {
                $this->adicionaLancamentoFinanceiro($inscrito->agendaInterlab, $inscrito->empresa, $inscrito->laboratorio, $request->valor);
            }

            

            $this->cancelEdit();
        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao processar: ' . $e->getMessage());
        }
        $this->showInscreveLab = false; // Esconde formulários
        $this->showSalvarEmpresa = false;
        $this->mount();

        
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

    // novo laboratorio para empresa ja inscrita
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

    public function buscaCep($campo) // Deve ser substituído por um método que traga apenas endereco de cobrança para empresas?
    {
        $cep = $campo === 'cobranca' ? $this->cobranca_cep : $this->cep;
        $cep = preg_replace('/\D/', '', $cep); 
        if (strlen($cep) === 8) { 
            $localEndereco = Endereco::where('cep', $cep)
                ->orderBy('id', 'desc')
                ->first();

            if ($localEndereco) {
                if ($campo === 'cobranca') {
                    $this->cobranca_endereco = $localEndereco->endereco;
                    $this->cobranca_bairro = $localEndereco->bairro;
                    $this->cobranca_cidade = $localEndereco->cidade;
                    $this->cobranca_uf = $localEndereco->uf;
                } else {
                    $this->endereco = $localEndereco->endereco;
                    $this->bairro = $localEndereco->bairro;
                    $this->cidade = $localEndereco->cidade;
                    $this->uf = $localEndereco->uf;
                }
            } else {
                $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");

                if ($response->successful() && !isset($response->json()['erro'])) {
                    $data = $response->json();
                    if ($campo === 'cobranca') {
                        $this->cobranca_endereco = $data['logradouro'] ?? '';
                        $this->cobranca_bairro = $data['bairro'] ?? '';
                        $this->cobranca_cidade = $data['localidade'] ?? '';
                        $this->cobranca_uf = $data['uf'] ?? '';
                    } else {
                        $this->endereco = $data['logradouro'] ?? '';
                        $this->bairro = $data['bairro'] ?? '';
                        $this->cidade = $data['localidade'] ?? '';
                        $this->uf = $data['uf'] ?? '';
                    }
                } else {
                    session()->flash('error', 'CEP não encontrado.');
                }
            }
        } else {
            session()->flash('error', 'CEP inválido. Certifique-se de que possui 8 dígitos.');
        }
    }

    public function encerrarInscricoes()
    {
        session()->forget(['interlab', 'empresa', 'convite']);
        return redirect('painel');
    }
    
}
