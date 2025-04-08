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
    public $empresas_inscritas; // empresas que o usuario ja tenha inscrito
    public $inscritos; //interlabs que usuario ja esta inscrito

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
        $this->empresas_inscritas = Pessoa::whereIn('id', $empresaIds)
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
            'BuscaCnpj.cnpj' => 'Não é um CNPJ válido.',
            'BuscaCnpj.required' => 'Digite um CNPJ para cadastro.',
        ]);

        $cnpjLimpo = preg_replace('/[^0-9]/', '', $this->BuscaCnpj);

        $empresa = Pessoa::with('enderecoCobranca')
            ->where('cpf_cnpj', $cnpjLimpo)
            ->where('tipo_pessoa', 'PJ')
            ->first();

        if ($empresa) {
            $this->empresa = $empresa->toArray();
        } else {
            $this->empresa = [
                'cpf_cnpj' => $cnpjLimpo,
            ];
        }
        $this->showSalvarEmpresa = true;
        $this->showInscreveLab = false;
        $this->BuscaCnpj = null;
    }

    //metodo para salvar empresa
    public function salvarEmpresa()
    {
        $this->validate(
            [
                'empresa.nome_razao' => ['required', 'string', 'max:191'],
                'empresa.cpf_cnpj' => ['required', 'cpf_ou_cnpj', 'max:191'],
                'empresa.endereco_cobranca.email' => ['required', 'email', 'max:191'],
                'empresa.endereco_cobranca.cep' => ['required', 'string'],
                'empresa.endereco_cobranca.endereco' => ['required', 'string'],
                'empresa.endereco_cobranca.bairro' => ['required', 'string'],
                'empresa.endereco_cobranca.cidade' => ['required', 'string'],
                'empresa.endereco_cobranca.uf' => ['required', 'string', 'size:2'],
            ],
            [
                'empresa.nome_razao.required' => 'Preencha o campo nome/razão social.',
                'empresa.nome_razao.max' => 'O campo nome/razão social deve ter no máximo :max caracteres.',
                'empresa.cpf_cnpj.required' => 'Preencha o campo CPF/CNPJ.',
                'empresa.endereco_cobranca.cep.required' => 'Preencha o campo CEP de cobrança.',
                'empresa.endereco_cobranca.endereco.required' => 'Preencha o campo endereço de cobrança.',
                'empresa.endereco_cobranca.bairro.required' => 'Preencha o campo bairro de cobrança.',
                'empresa.endereco_cobranca.email.required' => 'O email de cobrança é obrigatório.',
                'empresa.endereco_cobranca.email.email' => 'O email de cobrança deve ser um endereço de email válido.',
                'empresa.endereco_cobranca.cidade.required' => 'Preencha o campo cidade de cobrança.',
                'empresa.endereco_cobranca.uf.required' => 'Preencha o campo UF de cobrança.',
                'empresa.endereco_cobranca.uf.size' => 'O campo UF de cobrança deve ter exatamente 2 caracteres.',
            ]
        );

        // Atualiza ou cria a empresa
        $empresa = Pessoa::updateOrCreate(
            ['id' => $this->empresa['id'] ?? null],
            [
                'nome_razao' => $this->empresa['nome_razao'],
                'cpf_cnpj' => $this->empresa['cpf_cnpj'],
                'telefone' => $this->empresa['telefone'],
                'tipo_pessoa' => 'PJ',
            ]
        );

        // Atualiza ou cria o endereço de cobrança
        $enderecoCobranca = $empresa->enderecoCobranca()->updateOrCreate(
            ['pessoa_id' => $empresa->id],
            [
                'info' => 'Cobrança',
                'cep' => $this->empresa['endereco_cobranca']['cep'],
                'endereco' => $this->empresa['endereco_cobranca']['endereco'],
                'complemento' => $this->empresa['endereco_cobranca']['complemento'] ?? null,
                'bairro' => $this->empresa['endereco_cobranca']['bairro'],
                'cidade' => $this->empresa['endereco_cobranca']['cidade'],
                'uf' => $this->empresa['endereco_cobranca']['uf'],
                'email' => $this->empresa['endereco_cobranca']['email'],
                'cobranca' => 1,
            ]
        );

        // Atualiza o campo end_cobranca na tabela Pessoa
        $empresa->update([
            'end_cobranca' => $enderecoCobranca->id,
        ]);
        
        $this->empresa = $empresa->toArray();
        $this->showSalvarEmpresa = false;

        // Atualiza os dados necessários
        $this->inscritos = InterlabInscrito::with('laboratorio')
            ->where('pessoa_id', auth()->user()->pessoa->id)
            ->where('agenda_interlab_id', $this->interlab->id)
            ->get();

        $empresaIds = $this->inscritos->pluck('empresa_id')->unique();

        $this->empresas_inscritas = Pessoa::whereIn('id', $empresaIds)
            ->with('interlabs')
            ->get();

        $this->empresaEditadaId = null;

        if (in_array($empresa->id, $empresaIds->toArray())) {
            $this->showInscreveLab = false;
        } else {
            $this->showInscreveLab = true; // Mostra formulário de laboratório
        }
    }

    //metodo para editar empresas (preenche os campos)
    public function editEmpresa($empresaId)
    {
        $this->empresaEditadaId = $empresaId;

        // Carrega a empresa com o relacionamento enderecoCobranca
        $empresa = Pessoa::with('enderecoCobranca')->find($empresaId);
        $this->empresa = $empresa->toArray(); 
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

    // Metodo  salva interlab-inscritos
    public function InscreveLab()
    {
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

                    $endereco->update([
                        'cep' => $validated['cep'],
                        'endereco' => $validated['endereco'],
                        'complemento' => $validated['complemento'],
                        'bairro' => $validated['bairro'],
                        'cidade' => $validated['cidade'],
                        'uf' => $validated['uf'],
                    ]);

                    $laboratorio->update([
                        'nome' => $validated['laboratorio'],
                        'responsavel_tecnico' => $validated['responsavel_tecnico'],
                        'telefone' => $validated['lab_telefone'],
                        'email' => $validated['lab_email'],
                    ]);

                    InterlabInscrito::where('id', $this->inscritoEditadoId)->update([
                        'valor' => $validated['valor'] ?? null,
                        'informacoes_inscricao' => $validated['informacoes_inscricao'],
                    ]);
                } else {
                    // NOVO CADASTRO
                    $empresaId = $this->novaInscricaoEmpresaId ?? $this->empresa['id'];

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

                    $inscrito = InterlabInscrito::create([
                        'pessoa_id' => $this->pessoaId_usuario,
                        'empresa_id' => $empresaId,
                        'laboratorio_id' => $laboratorio->id,
                        'agenda_interlab_id' => $this->interlab->id,
                        'data_inscricao' => now(),
                        'valor' => $validated['valor'] ?? null,
                        'informacoes_inscricao' => $validated['informacoes_inscricao'],
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

        // Carrega o inscrito com o laboratório e endereço relacionados
        $inscrito = InterlabInscrito::with(['laboratorio.endereco'])->findOrFail($inscritoId);

        if ($inscrito) {
            // Preenche os campos do laboratório
            $this->laboratorio = $inscrito->laboratorio->nome;
            $this->responsavel_tecnico = $inscrito->laboratorio->responsavel_tecnico;
            $this->lab_telefone = $inscrito->laboratorio->telefone;
            $this->lab_email = $inscrito->laboratorio->email;
            $this->informacoes_inscricao = $inscrito->informacoes_inscricao;
            $this->valor = $inscrito->valor;

            // Preenche os campos do endereço do laboratório
            $this->cep = $inscrito->laboratorio->endereco->cep;
            $this->endereco = $inscrito->laboratorio->endereco->endereco;
            $this->complemento = $inscrito->laboratorio->endereco->complemento;
            $this->bairro = $inscrito->laboratorio->endereco->bairro;
            $this->cidade = $inscrito->laboratorio->endereco->cidade;
            $this->uf = $inscrito->laboratorio->endereco->uf;

            // Define o ID do laboratório que está sendo editado
            $this->laboratorioEditadoId = $inscrito->laboratorio->id;
        } else {
            session()->flash('error', 'Laboratório não encontrado.');
        }
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

    public function buscaCep($campo)
    {
        $cep = $campo === 'cobranca' ? $this->empresa['endereco_cobranca']['cep'] : $this->cep;
        $cep = preg_replace('/\D/', '', $cep);

        if (strlen($cep) === 8) {
            $localEndereco = Endereco::where('cep', $cep)
                ->orderBy('id', 'desc')
                ->first();

            if ($localEndereco) {
                if ($campo === 'cobranca') {
                    $this->empresa['endereco_cobranca']['endereco'] = $localEndereco->endereco;
                    $this->empresa['endereco_cobranca']['bairro'] = $localEndereco->bairro;
                    $this->empresa['endereco_cobranca']['cidade'] = $localEndereco->cidade;
                    $this->empresa['endereco_cobranca']['uf'] = $localEndereco->uf;
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
                        $this->empresa['endereco_cobranca']['endereco'] = $data['logradouro'] ?? '';
                        $this->empresa['endereco_cobranca']['bairro'] = $data['bairro'] ?? '';
                        $this->empresa['endereco_cobranca']['cidade'] = $data['localidade'] ?? '';
                        $this->empresa['endereco_cobranca']['uf'] = $data['uf'] ?? '';
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
