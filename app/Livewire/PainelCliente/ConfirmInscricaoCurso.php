<?php

namespace App\Livewire\PainelCliente;

use App\Models\User;
use App\Models\Pessoa;
use Livewire\Component;
use App\Models\Endereco;
use App\Models\AgendaCursos;
use Illuminate\Http\Request;
use App\Models\CursoInscrito;
use App\Models\LancamentoFinanceiro;
use Illuminate\Support\Facades\Http;

class ConfirmInscricaoCurso extends Component
{
    public $agendacurso;
    public $curso;
    public $pessoaId_usuario;
    public $jaInscrito = false;
    public $showTipoInscricao = true;
    public $tipoInscricao = '';
    public $BuscaCnpj;
    public $empresa;
    public $showSalvarEmpresa = false;
    public $editandoEmpresa = false;
    public $showBuscaCnpj = false;
    public $inscricoes = [
        ['id_pessoa' => '', 'nome' => '', 'email' => '', 'telefone' => '', 'cpf_cnpj' => '', 'responsavel' => 0],
    ];

    public function mount()
    {
        $this->pessoaId_usuario = auth()->user()->pessoa->id;
        $this->curso = session('curso');
        $this->agendacurso = AgendaCursos::where('id', session('curso')->id ?? null)->with('curso')->first();
        // Verifica se o usuário já está inscrito no agendacurso
        $this->jaInscrito = CursoInscrito::where('agenda_curso_id', $this->agendacurso->id ?? null)
            ->where('pessoa_id', $this->pessoaId_usuario)
            ->exists();
    }

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
        // Inicializa o array endereco_cobranca se não existir
        if (!isset($this->empresa['endereco_cobranca'])) {
            $this->empresa['endereco_cobranca'] = [];
        }
        $this->showSalvarEmpresa = true;
        $this->showBuscaCnpj = null;
        $this->BuscaCnpj = null;
    }

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

        $this->showSalvarEmpresa = false;
    }

    public function editarEmpresa()
    {
        $this->editandoEmpresa = true;
        $this->showSalvarEmpresa = true;
    }

    public function buscaCep()
    {
        if ($this->tipoInscricao === 'CNPJ') {
            $cep = $this->empresa['endereco_cobranca']['cep'] ?? $this->cep;
        } elseif ($this->tipoInscricao === 'CPF') {
            $cep = $this->inscricoes[0]['cep'] ?? '';
        }
        $cep = preg_replace('/\D/', '', $cep);

        if (empty($cep)) {
            session()->flash('error', 'O campo CEP está vazio. Por favor, insira um CEP válido.');
            return;
        }

        if (strlen($cep) === 8) {
            $localEndereco = Endereco::where('cep', $cep)
                ->orderBy('id', 'desc')
                ->first();

            if ($localEndereco) {
                if ($this->tipoInscricao === 'CNPJ') {
                    $this->empresa['endereco_cobranca']['endereco'] = $localEndereco->endereco;
                    $this->empresa['endereco_cobranca']['bairro'] = $localEndereco->bairro;
                    $this->empresa['endereco_cobranca']['cidade'] = $localEndereco->cidade;
                    $this->empresa['endereco_cobranca']['uf'] = $localEndereco->uf;
                } elseif ($this->tipoInscricao === 'CPF') {
                    $this->inscricoes[0]['endereco'] = $localEndereco->endereco;
                    $this->inscricoes[0]['bairro'] = $localEndereco->bairro;
                    $this->inscricoes[0]['cidade'] = $localEndereco->cidade;
                    $this->inscricoes[0]['uf'] = $localEndereco->uf;
                }
            } else {
                $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");

                if ($response->successful() && !isset($response->json()['erro'])) {
                    $data = $response->json();

                    if ($this->tipoInscricao === 'CNPJ') {
                        $this->empresa['endereco_cobranca']['endereco'] = $data['logradouro'] ?? '';
                        $this->empresa['endereco_cobranca']['bairro'] = $data['bairro'] ?? '';
                        $this->empresa['endereco_cobranca']['cidade'] = $data['localidade'] ?? '';
                        $this->empresa['endereco_cobranca']['uf'] = $data['uf'] ?? '';
                    } elseif ($this->tipoInscricao === 'CPF') {
                        $this->inscricoes[0]['endereco'] = $data['logradouro'] ?? '';
                        $this->inscricoes[0]['bairro'] = $data['bairro'] ?? '';
                        $this->inscricoes[0]['cidade'] = $data['localidade'] ?? '';
                        $this->inscricoes[0]['uf'] = $data['uf'] ?? '';
                    }
                } else {
                    session()->flash('error', 'CEP não encontrado.');
                }
            }
        } else {
            session()->flash('error', 'CEP inválido. Certifique-se de que possui 8 dígitos.');
        }
    }

    public function inscreverCNPJ()
    {
        $this->tipoInscricao = 'CNPJ';
        $this->showBuscaCnpj = true;
        $this->showTipoInscricao = false;
    }

    public function inscreverCPF()
    {
        $this->tipoInscricao = 'CPF';
        $this->showTipoInscricao = false;

        $usuario = auth()->user();
        if ($usuario && $usuario->pessoa) {
            $pessoa = $usuario->pessoa;
            $endereco = $pessoa->enderecos->first();

            $this->inscricoes[0] = [
                'id_pessoa' => $pessoa->id,
                'nome' => $pessoa->nome_razao ?? '',
                'email' => strtolower(trim($usuario->email)),
                'telefone' => $pessoa->telefone ?? '',
                'cpf_cnpj' => $pessoa->cpf_cnpj ?? '',
                'cep' => $endereco->cep ?? '',
                'endereco' => $endereco->endereco ?? '',
                'complemento' => $endereco->complemento ?? '',
                'bairro' => $endereco->bairro ?? '',
                'cidade' => $endereco->cidade ?? '',
                'uf' => $endereco->uf ?? '',
                'responsavel' => 1,
            ];
        }
    }

    public function adicionarInscricao()
    {
        $this->validateInscricao();
        $this->inscricoes[] = ['id_pessoa' => '', 'nome' => '', 'email' => '', 'telefone' => '', 'cpf_cnpj' => '', 'responsavel' => 0];
    }

    public function removerInscricao($index)
    {
        if (count($this->inscricoes) > 1) {
            unset($this->inscricoes[$index]);
            $this->inscricoes = array_values($this->inscricoes);
        }
        $this->validateInscricao();
    }

    private function validateInscricao()
    {
        $this->validate([
            'inscricoes.*.nome' => 'required|string|max:191',
            'inscricoes.*.email' => 'required|email|max:191|distinct',
            'inscricoes.*.telefone' => 'required_if:inscricoes.*.responsavel,1|string|min:10|max:15',
            'inscricoes.*.cpf_cnpj' => 'required_if:inscricoes.*.responsavel,1|string|min:11|max:14',
        ], [
            'inscricoes.*.nome.required' => 'O campo nome é obrigatório.',
            'inscricoes.*.nome.max' => 'O campo nome deve ter no máximo 191 caracteres.',
            'inscricoes.*.email.required' => 'O campo email é obrigatório.',
            'inscricoes.*.email.email' => 'O campo email deve ser um endereço válido.',
            'inscricoes.*.email.max' => 'O campo email deve ter no máximo 191 caracteres.',
            'inscricoes.*.email.distinct' => 'Não é permitido cadastrar e-mails duplicados.',
            'inscricoes.*.telefone.required_if' => 'O campo telefone é obrigatório.',
            'inscricoes.*.telefone.min' => 'O telefone deve ter no mínimo 10 caracteres.',
            'inscricoes.*.telefone.max' => 'O telefone deve ter no máximo 15 caracteres.',
            'inscricoes.*.cpf_cnpj.required_if' => 'O campo CPF/CNPJ é obrigatório.',
            'inscricoes.*.cpf_cnpj.min' => 'O CPF/CNPJ deve ter no mínimo 11 caracteres.',
            'inscricoes.*.cpf_cnpj.max' => 'O CPF/CNPJ deve ter no máximo 14 caracteres.',
        ]);
    }

    public function updatedInscricoes($value, $name)
    {
        // Verifica se o campo alterado é um e-mail
        if (str_contains($name, '.email')) {
            // Extrai o índice da inscrição
            preg_match('/(\d+)/', $name, $matches);
            $index = $matches[1] ?? null;

            if ($index !== null && isset($this->inscricoes[$index])) {
                // Normaliza o e-mail para letras minúsculas
                $this->inscricoes[$index]['email'] = strtolower(trim($this->inscricoes[$index]['email']));

                $emailInformado = $this->inscricoes[$index]['email'];

                if ($emailInformado === auth()->user()->email && $this->jaInscrito) {
                    session()->flash("error_$index", 'Você já está cadastrado neste curso.');
                    $this->inscricoes[$index]['email'] =  '';
                    $this->inscricoes[$index]['id_pessoa'] =  '';
                    $this->inscricoes[$index]['nome'] =  '';
                    $this->inscricoes[$index]['telefone'] = '';
                    $this->inscricoes[$index]['cpf_cnpj'] =  '';
                    return;
                }

                // Verifica se já existe um responsável na lista
                $responsavelExistente = collect($this->inscricoes)->contains('responsavel', 1);
                // Define o responsável apenas se ainda não houver um
                if (!$responsavelExistente && $emailInformado === auth()->user()->email) {
                    $this->inscricoes[$index]['responsavel'] = 1;
                } else {
                    $this->inscricoes[$index]['responsavel'] = 0;
                }

                // Verifica se existe um usuário com o e-mail informado
                $usuario = User::where('email', $emailInformado)->first();

                if ($usuario && $usuario->pessoa) {
                    $pessoa = $usuario->pessoa;
                    $this->inscricoes[$index]['id_pessoa'] = $pessoa->id ?? '';
                    $this->inscricoes[$index]['nome'] = $pessoa->nome_razao ?? '';
                    $this->inscricoes[$index]['telefone'] = $pessoa->telefone ?? '';
                    $this->inscricoes[$index]['cpf_cnpj'] = $pessoa->cpf_cnpj ?? '';
                }
            }
        }
    }

    public function salvarInscricaoCPF()
    {
        $inscricao = $this->inscricoes[0];
        $inscrito = Pessoa::where('id', $inscricao['id_pessoa'])
            ->where('tipo_pessoa', 'PF')
            ->first();
        if ($inscrito) {
            $inscrito->update([
                'nome_razao' => $inscricao['nome'],
                'email' => $inscricao['email'],
                'telefone' => $inscricao['telefone'],
            ]);
        }
        if ($inscrito) {
            Endereco::updateOrCreate(
                [
                    'pessoa_id' => $inscrito->id
                ],
                [
                    'cep' => $inscricao['cep'],
                    'uf' => $inscricao['uf'],
                    'endereco' => $inscricao['endereco'],
                    'complemento' => $inscricao['complemento'],
                    'bairro' => $inscricao['bairro'],
                    'cidade' => $inscricao['cidade'],
                ]
            );
            CursoInscrito::updateOrCreate(
                [
                    'pessoa_id' => $inscrito->id,
                    'agenda_curso_id' => $this->agendacurso->id,
                ],
                [
                    'valor' => $this->agendacurso->investimento,
                    'data_inscricao' => now(),
                ]
            );
            // Adiciona lançamento financeiro
            $lancamento = LancamentoFinanceiro::create([
                'pessoa_id' => $inscrito->id,
                'agenda_curso_id' => $this->agendacurso->id,
                'historico' => 'Inscrição no curso - ' . $this->agendacurso->curso->descricao,
                'valor' => formataMoeda($this->agendacurso->investimento),
                'centro_custo_id' => '3', // TREINAMENTO
                'plano_conta_id' => '3', // RECEITA PRESTAÇÃO DE SERVIÇOS
                'data_emissao' => now(),
                'status' => 'PROVISIONADO',
            ]);
        }
    }

    public function salvarInscricoes()
    {
        $this->validateInscricao();

        if ($this->tipoInscricao === 'CNPJ') {
            $this->salvarInscricaoCNPJ();
        } elseif ($this->tipoInscricao === 'CPF') {
            $this->salvarInscricaoCPF();
        }
        
        // limpa os dados da sessão e volta para o painel
        session()->forget(['curso', 'empresa', 'convite']);
        return redirect('painel');
    }
    

    public function cancelarInscricao()
    {
        $this->showTipoInscricao = true;
        $this->showBuscaCnpj = false;
        $this->showSalvarEmpresa = false;
        $this->editandoEmpresa = false;
        $this->empresa = null;
        $this->inscricoes = [
            ['id_pessoa' => '', 'nome' => '', 'email' => '', 'telefone' => '', 'cpf_cnpj' => '', 'cep' => '', 'endereco' => '', 'complemento' => '', 'bairro' => '', 'cidade' => '', 'uf' => '', 'responsavel' => 0],
        ];
    }

    public function render()
    {
        return view('livewire.painel-cliente.confirm-inscricao-curso', []);
    }
}

  