<?php

namespace App\Livewire\PainelCliente;

use App\Models\User;
use App\Models\Pessoa;
use Livewire\Component;
use App\Models\Endereco;
use App\Models\AgendaCursos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ConfirmInscricaoCurso extends Component
{
    public $curso;
    public $pessoaId_usuario;
    public $showTipoInscricao = true;
    public $tipoInscricao = '';
    public $BuscaCnpj;
    public $empresa;
    public $empresa_inscrita;
    public $showSalvarEmpresa = false;
    public $showBuscaCnpj = false;
    public $cep;
    public $inscricoes = [
        ['nome' => '', 'email' => '', 'telefone' => '', 'cpf_cnpj' => ''],
    ];

    public function mount()
    {
        $this->pessoaId_usuario = auth()->user()->pessoa->id;
        $this->curso = session('curso') ?? null;
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
        
        // $this->empresa = $empresa->toArray();
        $this->showSalvarEmpresa = false;

    }

    public function buscaCep()
    {
        $cep = $this->empresa['endereco_cobranca']['cep'] ?? $this->cep;
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
                $this->empresa['endereco_cobranca']['endereco'] = $localEndereco->endereco;
                $this->empresa['endereco_cobranca']['bairro'] = $localEndereco->bairro;
                $this->empresa['endereco_cobranca']['cidade'] = $localEndereco->cidade;
                $this->empresa['endereco_cobranca']['uf'] = $localEndereco->uf;
            } else {
                $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");

                if ($response->successful() && !isset($response->json()['erro'])) {
                    $data = $response->json();

                    $this->empresa['endereco_cobranca']['endereco'] = $data['logradouro'] ?? '';
                    $this->empresa['endereco_cobranca']['bairro'] = $data['bairro'] ?? '';
                    $this->empresa['endereco_cobranca']['cidade'] = $data['localidade'] ?? '';
                    $this->empresa['endereco_cobranca']['uf'] = $data['uf'] ?? '';
                } else {
                    session()->flash('error', 'CEP não encontrado.');
                }
            }
        } else {
            session()->flash('error', 'CEP inválido. Certifique-se de que possui 8 dígitos.');
        }
    }

    public function adicionarInscricao()
    {
        $this->validateInscricao();
        $this->inscricoes[] = ['nome' => '', 'email' => ''];
    }

    public function removerInscricao($index)
    {
        if (count($this->inscricoes) > 1) {
            unset($this->inscricoes[$index]);
            $this->inscricoes = array_values($this->inscricoes);
        }
    }

    private function validateInscricao()
    {
        $this->validate([
            'inscricoes.*.nome' => 'required|string|max:191',
            'inscricoes.*.email' => 'required|email|max:191|distinct',
        ], [
            'inscricoes.*.nome.required' => 'O campo nome é obrigatório.',
            'inscricoes.*.nome.max' => 'O campo nome deve ter no máximo 191 caracteres.',
            'inscricoes.*.email.required' => 'O campo email é obrigatório.',
            'inscricoes.*.email.email' => 'O campo email deve ser um endereço válido.',
            'inscricoes.*.email.max' => 'O campo email deve ter no máximo 191 caracteres.',
            'inscricoes.*.email.distinct' => 'Não é permitido cadastrar e-mails duplicados.',
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

                // Verifica se existe um usuário com o e-mail informado
                $usuario = User::where('email', $emailInformado)->first();

                if ($usuario && $usuario->pessoa) {
                    $pessoa = $usuario->pessoa;

                    $this->inscricoes[$index]['nome'] = $pessoa->nome_razao ?? '';
                    $this->inscricoes[$index]['telefone'] = $pessoa->telefone ?? '';
                    $this->inscricoes[$index]['cpf_cnpj'] = $pessoa->cpf_cnpj ?? '';
                } else {
                    // Caso não encontre correspondência, limpa os campos
                    $this->inscricoes[$index]['nome'] = '';
                    $this->inscricoes[$index]['telefone'] = '';
                    $this->inscricoes[$index]['cpf_cnpj'] = '';
                }
            }
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

        // Adiciona automaticamente os dados do usuário logado na lista de inscrições
        $usuario = auth()->user();
        if ($usuario && $usuario->pessoa) {
            $pessoa = $usuario->pessoa;
            $this->inscricoes[0] = [
                'nome' => $pessoa->nome_razao ?? '',
                'email' => strtolower(trim($usuario->email)),
                'telefone' => $pessoa->telefone ?? '',
                'cpf_cnpj' => $pessoa->cpf_cnpj ?? '',
            ];
        }
    }

    public function render()
    {
        return view('livewire.painel-cliente.confirm-inscricao-curso', [
        ]);
    }
}
