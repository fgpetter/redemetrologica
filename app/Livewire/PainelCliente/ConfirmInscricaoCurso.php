<?php

namespace App\Livewire\PainelCliente;

use App\Actions\NotifyInscricaoCursoAction;
use App\Actions\SalvaInscritoAction;
use App\Actions\SalvarEmpresaInscricaoAction;
use App\Helpers\Telefone;
use App\Models\AgendaCursos;
use App\Models\CursoInscrito;
use App\Models\Endereco;
use App\Models\LancamentoFinanceiro;
use App\Models\Pessoa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ConfirmInscricaoCurso extends Component
{
    public $agendacurso;

    public $curso;

    #[Locked]
    public $pessoaId_usuario;

    public $jaInscrito = false;

    /**
     * Inscrição no curso como pessoa física (sem empresa no vínculo).
     */
    public bool $jaInscritoCpf = false;

    /**
     * Inscrição no curso vinculada a empresa (CNPJ).
     */
    public bool $jaInscritoCnpj = false;

    public $showTipoInscricao = true;

    public $tipoInscricao = '';

    public $BuscaCnpj;

    #[Locked]
    public ?int $empresaId = null;

    public $empresa;

    public $showSalvarEmpresa = false;

    public $editandoEmpresa = false;

    public $showBuscaCnpj = false;

    public $inscricoes = [
        ['id_pessoa' => '', 'nome' => '', 'email' => '', 'telefone' => '', 'cpf_cnpj' => '', 'responsavel' => 0],
    ];

    public $MeInscrever = false;

    public array $dadosCobranca = [
        'forma_pagamento' => null,
        'exige_pedido_compra' => null,
        'entidade_governamental' => null,
        'esfera_governamental' => null,
    ];

    public function mount() // metodo chamado quando o componente é montado
    {
        $user = auth()->user();
        if ($user->pessoa === null) {
            throw new \LogicException('Usuário autenticado sem pessoa vinculada ao carregar inscrição em curso.');
        }

        $this->pessoaId_usuario = $user->pessoa->id;
        $this->curso = session('curso');
        $this->agendacurso = AgendaCursos::where('id', session('curso')->id ?? null)->with('curso')->first();
        $agendaCursoId = $this->agendacurso->id ?? null;

        $this->jaInscritoCpf = CursoInscrito::query()
            ->where('agenda_curso_id', $agendaCursoId)
            ->where('pessoa_id', $this->pessoaId_usuario)
            ->whereNull('empresa_id')
            ->exists();

        $this->jaInscritoCnpj = CursoInscrito::query()
            ->where('agenda_curso_id', $agendaCursoId)
            ->where('pessoa_id', $this->pessoaId_usuario)
            ->whereNotNull('empresa_id')
            ->exists();

        $this->jaInscrito = $this->jaInscritoCpf || $this->jaInscritoCnpj;
    }

    public function ProcuraCnpj() // Método chamado quando o usuário clica no botão de busca de CNPJ
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

        $this->empresaId = $empresa?->id;
        if ($empresa) {
            $this->empresa = $empresa->toArray();
            $this->empresa['telefone'] = $empresa->getRawOriginal('telefone');
        } else {
            $this->empresa = [
                'cpf_cnpj' => $this->BuscaCnpj,
            ];
        }
        // Inicializa o array endereco_cobranca se não existir
        if (! isset($this->empresa['endereco_cobranca'])) {
            $this->empresa['endereco_cobranca'] = [];
        }
        $this->showSalvarEmpresa = true;
        $this->showBuscaCnpj = null;
        $this->BuscaCnpj = null;
    }

    public function salvarEmpresa(SalvarEmpresaInscricaoAction $salvarEmpresa): void
    {
        $this->empresa['telefone'] = Telefone::paraValidacao($this->empresa['telefone'] ?? null);
        $empresa = $salvarEmpresa->execute($this->empresaId, $this->empresa, $this->dadosCobranca);
        $this->empresaId = $empresa->id;
        $this->empresa = $empresa->toArray();
        $this->empresa['telefone'] = $empresa->getRawOriginal('telefone');

        $this->showSalvarEmpresa = false;
    }

    public function editarEmpresa() // Método chamado quando o usuário clica no botão de editar empresa
    {
        $this->editandoEmpresa = true;
        $this->showSalvarEmpresa = true;
    }

    public function buscaCep() // Método chamado quando digita CEP
    {
        if ($this->tipoInscricao === 'CNPJ') {
            $cep = $this->empresa['endereco_cobranca']['cep'] ?? '';
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

                if ($response->successful() && ! isset($response->json()['erro'])) {
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

    public function inscreverCNPJ() // Método chamado quando o usuário clica no botão de inscrever CNPJ
    {
        $this->tipoInscricao = 'CNPJ';
        $this->showBuscaCnpj = true;
        $this->showTipoInscricao = false;
    }

    public function inscreverCPF() // Método chamado quando o usuário clica no botão de inscrever CPF
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
                'telefone' => $pessoa->getRawOriginal('telefone') ?? '',
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

    public function adicionarInscricao() // Método chamado quando o usuário clica no +
    {
        $this->validateInscricao();
        $this->inscricoes[] = ['id_pessoa' => '', 'nome' => '', 'email' => '', 'telefone' => '', 'cpf_cnpj' => '', 'responsavel' => 0];
    }

    public function removerInscricao($index) // Método chamado quando o usuário clica no -
    {
        if (count($this->inscricoes) > 1) {
            unset($this->inscricoes[$index]);
            $this->inscricoes = array_values($this->inscricoes);
        }
        $this->validateInscricao();
    }

    private function validateInscricao() // Método para validar os dados da inscrição
    {
        foreach ($this->inscricoes as $index => $inscricao) {
            $this->inscricoes[$index]['telefone'] = Telefone::paraValidacao($inscricao['telefone'] ?? null);
        }

        $this->validate([
            'inscricoes.*.nome' => 'required|string|max:191',
            'inscricoes.*.email' => 'required|email|max:191|distinct',
            'inscricoes.*.telefone' => ['nullable', 'celular_com_ddd'],
        ], [
            'inscricoes.*.nome.required' => 'O campo nome é obrigatório.',
            'inscricoes.*.nome.max' => 'O campo nome deve ter no máximo 191 caracteres.',
            'inscricoes.*.email.required' => 'O campo email é obrigatório.',
            'inscricoes.*.email.email' => 'O campo email deve ser um endereço válido.',
            'inscricoes.*.email.max' => 'O campo email deve ter no máximo 191 caracteres.',
            'inscricoes.*.email.distinct' => 'Não é permitido cadastrar e-mails duplicados.',
            'inscricoes.*.telefone.celular_com_ddd' => 'Informe um telefone válido com DDD.',
        ]);
    }

    public function updatedInscricoes($value, $name) // Método chamado quando um campo da inscrição é atualizado
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
                    session()->flash("error_$index", 'Você já está inscrito neste curso.');
                    $this->inscricoes[$index]['email'] = '';
                    $this->inscricoes[$index]['id_pessoa'] = '';
                    $this->inscricoes[$index]['nome'] = '';
                    $this->inscricoes[$index]['telefone'] = '';
                    $this->inscricoes[$index]['cpf_cnpj'] = '';

                    return;
                }

                // Verifica se já existe um responsável na lista
                $responsavelExistente = collect($this->inscricoes)->contains('responsavel', 1);
                // Define o responsável apenas se ainda não houver um
                if (! $responsavelExistente && $emailInformado === auth()->user()->email) {
                    $this->inscricoes[$index]['responsavel'] = 1;
                } else {
                    $this->inscricoes[$index]['responsavel'] = 0;
                }

                // Verifica se existe um usuário com o e-mail informado
                // $usuario = User::where('email', $emailInformado)->first();

                // Adiciona o id_pessoa do responsavel pela inscrição[NEW]
                $this->inscricoes[$index]['id_pessoa'] = $this->pessoaId_usuario;

            }
        }
    }

    private function incluirMeInscrever() // metodo que quando marcado adiciona uma linha em incricoes com os dados da pessoa do usuario.
    {
        if ($this->MeInscrever) {
            $user = auth()->user();
            $pessoa = $user->pessoa;
            // verifica se já não foi adicionado
            foreach ($this->inscricoes as $inscricao) {
                if (isset($inscricao['email']) && strtolower($inscricao['email']) == strtolower($user->email)) {
                    return;
                }
            }

            $this->inscricoes[] = [
                'id_pessoa' => $pessoa->id,
                'nome' => $pessoa->nome_razao,
                'email' => strtolower($user->email),
                'telefone' => $pessoa->getRawOriginal('telefone') ?? '',
                'responsavel' => 1,
            ];
            // Remove linhas vazias (onde nem nome nem email foram preenchidos)
            $this->inscricoes = array_values(array_filter($this->inscricoes, function ($inscricao) {
                return ! empty(trim($inscricao['nome'] ?? '')) || ! empty(trim($inscricao['email'] ?? ''));
            }));
        }
    }

    private function salvarInscricaoCNPJ(): void // Método com regras para salvar inscrição de CNPJ
    {
        $pessoaEmpresa = Pessoa::where('tipo_pessoa', 'PJ')->findOrFail($this->empresaId);
        $valor = $pessoaEmpresa->associado == 1
            ? $this->agendacurso->investimento_associado
            : $this->agendacurso->investimento;

        foreach ($this->inscricoes as $inscricao) {
            app(SalvaInscritoAction::class)->criar($this->agendacurso, [
                'tipo_inscricao' => 'cnpj',
                'dados_cobranca' => LancamentoFinanceiro::dadosCobrancaDoFormulario($this->dadosCobranca),
                'empresa_id' => $pessoaEmpresa->id,
                'nome' => $inscricao['nome'],
                'email' => $inscricao['email'],
                'telefone' => $inscricao['telefone'],
                'valor' => $valor,
            ], enviarEmail: false);
        }
    }

    private function salvarInscricaoCPF(): void // Método com regras para salvar inscrição de CPF
    {
        $pessoaUsuario = auth()->user()->pessoa;
        $inscricao = $this->inscricoes[0];
        $valor = $pessoaUsuario->associado == 1
            ? $this->agendacurso->investimento_associado
            : $this->agendacurso->investimento;

        app(SalvaInscritoAction::class)->criar($this->agendacurso, [
            'tipo_inscricao' => 'cpf',
            'idempotente' => true,
            'pessoa_id' => $pessoaUsuario->id,
            'nome' => $inscricao['nome'],
            'email' => $inscricao['email'],
            'telefone' => $inscricao['telefone'],
            'cpf' => $inscricao['cpf_cnpj'],
            'cep' => $inscricao['cep'],
            'uf' => $inscricao['uf'],
            'endereco' => $inscricao['endereco'],
            'complemento' => $inscricao['complemento'],
            'bairro' => $inscricao['bairro'],
            'cidade' => $inscricao['cidade'],
            'valor' => $valor,
        ], enviarEmail: false);
    }

    private function enviarConfirmacaoInscricao(): void
    {
        $participantes = collect($this->inscricoes)->map(fn (array $inscricao) => [
            'nome' => $inscricao['nome'],
            'email' => $inscricao['email'],
            'telefone' => $inscricao['telefone'] ?? '',
            'empresa_nome' => $this->empresa['nome_razao'] ?? null,
        ])->values()->all();

        app(NotifyInscricaoCursoAction::class)->execute(
            $this->agendacurso,
            $participantes,
            intervaloSegundos: 5
        );
    }

    public function salvarInscricoes() // método que consolida e conclui a inscrição
    {
        if ($this->tipoInscricao === 'CNPJ') {
            $this->incluirMeInscrever();
            $this->validateInscricao();
            DB::transaction(fn () => $this->salvarInscricaoCNPJ());
        } elseif ($this->tipoInscricao === 'CPF') {
            $this->validateInscricao();
            DB::transaction(fn () => $this->salvarInscricaoCPF());
        }

        $this->enviarConfirmacaoInscricao();

        // limpa os dados da sessão e volta para o painel
        session()->forget(['curso', 'empresa']);

        return redirect('painel');
    }

    public function cancelarInscricao() // método que cancela a inscrição
    {
        $this->showTipoInscricao = true;
        $this->showBuscaCnpj = false;
        $this->showSalvarEmpresa = false;
        $this->editandoEmpresa = false;
        $this->empresaId = null;
        $this->empresa = null;
        $this->inscricoes = [
            ['id_pessoa' => '', 'nome' => '', 'email' => '', 'telefone' => '', 'cpf_cnpj' => '', 'cep' => '', 'endereco' => '', 'complemento' => '', 'bairro' => '', 'cidade' => '', 'uf' => '', 'responsavel' => 0],
        ];
    }

    public function fecharInscricao() // método que fecha a inscrição
    {
        $this->showTipoInscricao = true;
        $this->showBuscaCnpj = false;
        $this->showSalvarEmpresa = false;
        $this->editandoEmpresa = false;
        $this->empresaId = null;
        $this->empresa = null;
        $this->inscricoes = [
            ['id_pessoa' => '', 'nome' => '', 'email' => '', 'telefone' => '', 'cpf_cnpj' => '', 'cep' => '', 'endereco' => '', 'complemento' => '', 'bairro' => '', 'cidade' => '', 'uf' => '', 'responsavel' => 0],
        ];
        session()->forget(['curso', 'empresa']);

        return redirect('painel');
    }

    public function render() // método que renderiza a view
    {
        return view('livewire.painel-cliente.confirm-inscricao-curso', []);
    }
}
