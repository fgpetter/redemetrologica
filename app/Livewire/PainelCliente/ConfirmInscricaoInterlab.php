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
    public $empresa; //empresa encontrada em ProcuraCnpj
    public $empresas_inscritas; // empresas que o usuario ja tenha inscrito
    public $inscritos; //interlabs que usuario ja esta inscrito

    // Dados da inscrição interlab
    public $laboratorio;
    public $informacoes_inscricao;
    public $valor;

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

        // chama eventos do tour
        if ($this->empresas_inscritas->isEmpty()) {
            $this->dispatch('start-tour-1'); //primeira inscrição
        } else {
            $this->dispatch('start-tour-4'); //tour final
        }

        $this->laboratorio = [
            'nome' => '',
            'responsavel_tecnico' => '',
            'telefone' => '',
            'email' => '',
            'endereco' => [
                'cep' => '',
                'endereco' => '',
                'complemento' => '',
                'bairro' => '',
                'cidade' => '',
                'uf' => '',
            ]
        ];


        $this->reset(['inscritoId', 'laboratorioId']);
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
        //carrega endereço de cobrança
        $empresa = Pessoa::with('enderecoCobranca')
            ->where('cpf_cnpj', $cnpjLimpo)
            ->where('tipo_pessoa', 'PJ')
            ->first();

        if ($empresa) {
            $this->empresa = $empresa->toArray();


            if (empty($this->empresa['endereco_cobranca'])) {
                // Se não tem endereço de cobrança, busca o endereço cadastrado
                $empresa_end = Pessoa::with('enderecos')
                    ->where('cpf_cnpj', $cnpjLimpo)
                    ->where('tipo_pessoa', 'PJ')
                    ->first();
                // Se tem apenas 1 endereço cadastrado, usa ele como cobrança
                if ($empresa_end && $empresa_end->enderecos->count() === 1) {
                    $endereco = $empresa_end->enderecos->first();
                    $this->empresa['endereco_cobranca'] = [
                        'cep' => $endereco['cep'],
                        'endereco' => $endereco['endereco'],
                        'complemento' => $endereco['complemento'] ?? '',
                        'bairro' => $endereco['bairro'],
                        'cidade' => $endereco['cidade'],
                        'uf' => $endereco['uf'],
                        'email' => $this->empresa['email'] ?? '',
                    ];
                } else {
                    // Se tem mais de 1 endereço cadastrado, deixa em branco
                    $this->empresa['endereco_cobranca'] = [
                        'cep' => '',
                        'endereco' => '',
                        'complemento' => '',
                        'bairro' => '',
                        'cidade' => '',
                        'uf' => '',
                        'email' => '',
                    ];
                }
            }
        } else {
            $this->empresa = [
                'cpf_cnpj' => $cnpjLimpo,
                'telefone' => '',
                'enderecos' => [],
                'endereco_cobranca' => [
                    'cep' => '',
                    'endereco' => '',
                    'complemento' => '',
                    'bairro' => '',
                    'cidade' => '',
                    'uf' => '',
                    'email' => '',
                ],
            ];
        }

        $this->showSalvarEmpresa = true;
        $this->showInscreveLab = false;
        $this->BuscaCnpj = null;
        // chama tour de cadastro de empresa
        $this->dispatch('start-tour-2');
    }

    //metodo para salvar empresa
    public function salvarEmpresa()
    {
        $this->validate(
            [
                'empresa.nome_razao' => ['required', 'string', 'max:191'],
                'empresa.cpf_cnpj' => ['required', 'cpf_ou_cnpj', 'max:191'],
                'empresa.endereco_cobranca.email' => ['required', 'email', 'max:191'],
                'empresa.endereco_cobranca.cep' => ['required', 'string', 'min:9'],
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
                'empresa.endereco_cobranca.cep.min' => 'O campo CEP de cobrança deve ter no mínimo :min caracteres.',
                'empresa.endereco_cobranca.endereco.required' => 'Preencha o campo endereço de cobrança.',
                'empresa.endereco_cobranca.bairro.required' => 'Preencha o campo bairro de cobrança.',
                'empresa.endereco_cobranca.email.required' => 'O email de cobrança é obrigatório.',
                'empresa.endereco_cobranca.email.email' => 'O email de cobrança deve ser um endereço de email válido.',
                'empresa.endereco_cobranca.cidade.required' => 'Preencha o campo cidade de cobrança.',
                'empresa.endereco_cobranca.uf.required' => 'Preencha o campo UF de cobrança.',
                'empresa.endereco_cobranca.uf.size' => 'O campo UF de cobrança deve ter exatamente 2 caracteres.',
            ]
        );

        $empresa = Pessoa::updateOrCreate(
            ['id' => $this->empresa['id'] ?? null],
            [
                'nome_razao' => $this->empresa['nome_razao'],
                'cpf_cnpj' => $this->empresa['cpf_cnpj'],
                'telefone' => $this->empresa['telefone'],
                'tipo_pessoa' => 'PJ',
            ]
        );

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

        $empresa->update([
            'end_cobranca' => $enderecoCobranca->id,
            'email_cobranca' => $enderecoCobranca->email, //registra na tabela de pessoas o email de cobrança
        ]);

        $this->empresa = $empresa->toArray();
        $this->showSalvarEmpresa = false;

        $this->inscritos = InterlabInscrito::with('laboratorio')
            ->where('pessoa_id', auth()->user()->pessoa->id)
            ->where('agenda_interlab_id', $this->interlab->id)
            ->get();

        $empresaIds = $this->inscritos->pluck('empresa_id')->unique();

        $this->empresas_inscritas = Pessoa::whereIn('id', $empresaIds)
            ->with('interlabs')
            ->get();



        if (in_array($empresa->id, $empresaIds->toArray())) {
            $this->showInscreveLab = false;
        } else {
            $this->showInscreveLab = true; // Mostra formulário de laboratório
            $this->dispatch('start-tour-3'); //chama tour de inscricao de laboratorio
        }


        $this->empresaEditadaId = null;
    }

    //metodo para editar empresas (preenche os campos)
    public function editEmpresa($empresaId)
    {
        $this->empresaEditadaId = $empresaId;
        $empresa = Pessoa::with('enderecoCobranca')->find($empresaId);
        $this->empresa = $empresa->toArray();
    }

    public function rules(): array
    {
        return [
            "laboratorio.nome" => ['required', 'string', 'max:191'],
            "laboratorio.responsavel_tecnico" => ['required', 'string', 'max:191'],
            "laboratorio.telefone" => ['nullable', 'string', 'min:10', 'max:11'],
            "laboratorio.email" => ['required', 'email', 'max:191'],
            "laboratorio.endereco.cep" => ['required', 'string', 'min:9'],
            "laboratorio.endereco.endereco" => ['required', 'string'],
            "laboratorio.endereco.complemento" => ['nullable', 'string'],
            "laboratorio.endereco.bairro" => ['required', 'string'],
            "laboratorio.endereco.cidade" => ['nullable', 'string'],
            "laboratorio.endereco.uf" => ['required', 'string', 'size:2'],
            "informacoes_inscricao" => ['required', 'string'],
            "valor" => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'laboratorio.nome.required' => 'Preencha o campo laboratório.',
            'laboratorio.nome.max' => 'O campo laboratório deve ter no máximo :max caracteres.',
            'laboratorio.responsavel_tecnico.required' => 'Preencha o campo responsável técnico.',
            'laboratorio.responsavel_tecnico.max' => 'O campo responsável técnico deve ter no máximo :max caracteres.',
            'laboratorio.telefone.*' => 'O telefone informado é inválido.',
            'laboratorio.email.required' => 'O email é obrigatório.',
            'laboratorio.email.email' => 'O email deve ser um endereço de email válido.',
            'laboratorio.endereco.cep.required' => 'Preencha o campo CEP.',
            'laboratorio.endereco.cep.min' => 'O campo CEP deve ter no mínimo :min caracteres.',
            'laboratorio.endereco.endereco.required' => 'Preencha o campo endereço.',
            'laboratorio.endereco.bairro.required' => 'Preencha o campo bairro.',
            'laboratorio.endereco.uf.required' => 'Preencha o campo UF.',
            'laboratorio.endereco.uf.size' => 'O campo UF deve ter exatamente 2 caracteres.',
            'valor.string' => 'O valor digitado é inválido.',
            'informacoes_inscricao.required' => 'Informe aqui quais rodadas, blocos ou parâmetros esse laboratório irá participar.',
        ];
    }


    // Metodo  salva interlab-inscritos
    public function InscreveLab()
    {
        if (!empty($this->laboratorio['telefone'])) {
            $this->laboratorio['telefone'] = preg_replace('/\D/', '', $this->laboratorio['telefone']);
        }
        $validated = $this->validate();

        try {
            $inscrito = DB::transaction(function () use ($validated) {
                if ($this->laboratorioEditadoId) {
                    // EDIÇÃO:
                    $laboratorio = InterlabLaboratorio::findOrFail($this->laboratorioEditadoId);
                    $empresaId = $laboratorio->empresa_id;
                    $endereco = Endereco::findOrFail($laboratorio->endereco_id);
                    
                    $endereco->update([
                        'cep' => $validated['laboratorio']['endereco']['cep'],
                        'endereco' => $validated['laboratorio']['endereco']['endereco'],
                        'complemento' => $validated['laboratorio']['endereco']['complemento'] ?? null,
                        'bairro' => $validated['laboratorio']['endereco']['bairro'],
                        'cidade' => $validated['laboratorio']['endereco']['cidade'],
                        'uf' => $validated['laboratorio']['endereco']['uf'],
                        'info' => 'Laboratório: ' . $validated['laboratorio']['nome'] . ' | Inscrito no PEP: ' . ($this->interlab->nome ?? ''), //adicionar a info do PEP
                    ]);

                    $laboratorio->update([
                        'nome' => $validated['laboratorio']['nome'],
                        'responsavel_tecnico' => $validated['laboratorio']['responsavel_tecnico'],
                        'telefone' => $validated['laboratorio']['telefone'],
                        'email' => $validated['laboratorio']['email'],
                    ]);

                    InterlabInscrito::where('id', $this->inscritoEditadoId)->update([
                        'valor' => $validated['valor'] ?? null,
                        'informacoes_inscricao' => $validated['informacoes_inscricao'],
                    ]);
                } else {
                    // NOVO CADASTRO:
                    $empresaId = $this->novaInscricaoEmpresaId ?? $this->empresa['id'];

                    $endereco = Endereco::create([
                        'pessoa_id' => $empresaId,
                        'info' => 'Laboratório: ' . $validated['laboratorio']['nome'] . ' | Inscrito no PEP: ' . ($this->interlab->nome ?? ''), //adicionar a info do PEP
                        'cep' => $validated['laboratorio']['endereco']['cep'],
                        'endereco' => $validated['laboratorio']['endereco']['endereco'],
                        'complemento' => $validated['laboratorio']['endereco']['complemento'] ?? null,
                        'bairro' => $validated['laboratorio']['endereco']['bairro'],
                        'cidade' => $validated['laboratorio']['endereco']['cidade'],
                        'uf' => $validated['laboratorio']['endereco']['uf'],
                    ]);

                    $laboratorio = InterlabLaboratorio::create([
                        'empresa_id' => $empresaId,
                        'endereco_id' => $endereco->id,
                        'nome' => $validated['laboratorio']['nome'],
                        'responsavel_tecnico' => $validated['laboratorio']['responsavel_tecnico'],
                        'telefone' => $validated['laboratorio']['telefone'],
                        'email' => $validated['laboratorio']['email'],
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

                    Mail::to('interlab@redemetrologica.com.br')
                        ->cc('tecnico@redemetrologica.com.br')
                        ->cc('sistema@redemetrologica.com.br')
                        ->send(new NovoCadastroInterlabNotification($inscrito, $this->interlab));
                        
                    Mail::mailer('interlaboratorial')
                        ->to($inscrito->pessoa->email)
                        ->cc('sistema@redemetrologica.com.br')
                        ->send(new ConfirmacaoInscricaoInterlabNotification($inscrito, $this->interlab));
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


            if (!empty($validated['valor']) && $validated['valor'] > 0) {
                $this->adicionaLancamentoFinanceiro(
                    $inscrito->agendaInterlab,
                    $inscrito->empresa,
                    $inscrito->laboratorio,
                    $validated['valor']
                );
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

        $this->laboratorio = $inscrito->laboratorio->toArray();
        $this->laboratorio['endereco'] = $inscrito->laboratorio->endereco
            ? $inscrito->laboratorio->endereco->toArray()
            : [];
        $this->laboratorioEditadoId = $inscrito->laboratorio->id;
        $this->informacoes_inscricao = $inscrito->informacoes_inscricao;
    }

    // novo laboratorio para empresa ja inscrita
    public function novoLaboratorio($empresaId)
    {
        $this->novaInscricaoEmpresaId = $empresaId;
        $this->reset(['laboratorioEditadoId', 'inscritoEditadoId']);
        $this->cancelEdit();
        $this->laboratorio = [
            'nome' => '',
            'responsavel_tecnico' => '',
            'telefone' => '',
            'email' => '',
            'endereco' => [
                'cep' => '',
                'endereco' => '',
                'complemento' => '',
                'bairro' => '',
                'cidade' => '',
                'uf' => '',
            ],
        ];
    }

    // Cancelar edições
    public function cancelEdit()
    {
        $this->reset([
            'laboratorioEditadoId',
            'inscritoEditadoId',
            'laboratorio',
            'informacoes_inscricao',
        ]);
    }

    public function buscaCep($campo)
    {
        $cep = $campo === 'cobranca'
            ? ($this->empresa['endereco_cobranca']['cep'] ?? '')
            : ($this->laboratorio['endereco']['cep'] ?? '');
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
                    $this->laboratorio['endereco']['endereco'] = $localEndereco->endereco;
                    $this->laboratorio['endereco']['bairro'] = $localEndereco->bairro;
                    $this->laboratorio['endereco']['cidade'] = $localEndereco->cidade;
                    $this->laboratorio['endereco']['uf'] = $localEndereco->uf;
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
                        $this->laboratorio['endereco']['endereco'] = $data['logradouro'] ?? '';
                        $this->laboratorio['endereco']['bairro'] = $data['bairro'] ?? '';
                        $this->laboratorio['endereco']['cidade'] = $data['localidade'] ?? '';
                        $this->laboratorio['endereco']['uf'] = $data['uf'] ?? '';
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
