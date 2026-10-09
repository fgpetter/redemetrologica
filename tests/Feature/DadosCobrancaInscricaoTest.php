<?php

use App\Actions\Financeiro\GerarLancamentoCursoAction;
use App\Actions\Financeiro\GerarLancamentoInterlabAction;
use App\Enums\EsferaGovernamental;
use App\Enums\FormaPagamentoCobranca;
use App\Livewire\PainelCliente\ConfirmaCNPJ;
use App\Livewire\PainelCliente\ConfirmInscricaoCurso;
use App\Models\AgendaCursos;
use App\Models\CentroCusto;
use App\Models\Curso;
use App\Models\Instrutor;
use App\Models\LancamentoFinanceiro;
use App\Models\Permission;
use App\Models\Pessoa;
use App\Models\PlanoConta;
use App\Models\User;
use Database\Factories\InterlabInscritoFactory;
use Database\Factories\LancamentoFinanceiroFactory;
use Illuminate\Support\Str;
use Livewire\Livewire;

function cobrancaFederal(): array
{
    return [
        'forma_pagamento' => 'boleto_bancario',
        'exige_pedido_compra' => true,
        'entidade_governamental' => true,
        'esfera_governamental' => 'federal',
    ];
}

function cobrancaNaoGovernamental(): array
{
    return [
        'forma_pagamento' => 'deposito_bancario',
        'exige_pedido_compra' => false,
        'entidade_governamental' => false,
        'esfera_governamental' => null,
    ];
}

function empresaComCobrancaValida(): array
{
    return [
        'id' => null,
        'nome_razao' => 'Empresa Teste LTDA',
        'cpf_cnpj' => '11222333000181',
        'telefone' => '(51) 99999-9999',
        'email' => '',
        'endereco_cobranca' => [
            'cep' => '90150-053',
            'endereco' => 'Rua Botafogo, 1051',
            'complemento' => '',
            'bairro' => 'Centro',
            'cidade' => 'Porto Alegre',
            'uf' => 'RS',
            'email' => 'financeiro@example.com',
        ],
    ];
}

function criarAgendaCursoComCobranca(): AgendaCursos
{
    $curso = Curso::query()->create([
        'descricao' => 'Curso cobrança teste',
        'tipo_curso' => 'OFICIAL',
    ]);

    $pessoaInstrutor = Pessoa::query()->create([
        'nome_razao' => 'Instrutor Teste',
        'cpf_cnpj' => str_pad((string) random_int(10000000000, 99999999999), 11, '0', STR_PAD_LEFT),
        'tipo_pessoa' => 'PF',
    ]);

    $instrutor = Instrutor::query()->create([
        'pessoa_id' => $pessoaInstrutor->id,
        'situacao' => true,
    ]);

    return AgendaCursos::query()->create([
        'uid' => (string) Str::uuid(),
        'curso_id' => $curso->id,
        'instrutor_id' => $instrutor->id,
        'status' => 'AGENDADO',
        'tipo_agendamento' => 'ONLINE',
        'inscricoes' => 1,
        'investimento' => 100,
        'investimento_associado' => 80,
    ]);
}

function seedCentroCustoParaCobranca(): void
{
    CentroCusto::query()->firstOrCreate(
        ['id' => CentroCusto::ID_TREINAMENTO],
        ['descricao' => 'Treinamento']
    );

    PlanoConta::query()->firstOrCreate(
        ['id' => PlanoConta::ID_RECEITA_PRESTACAO_SERVICOS],
        [
            'descricao' => 'Receita Prestação de Serviços',
            'centro_custo_id' => CentroCusto::ID_TREINAMENTO,
        ]
    );
}

function criarEmpresaPjParaCobranca(): Pessoa
{
    return Pessoa::query()->create([
        'nome_razao' => 'Empresa PJ',
        'cpf_cnpj' => str_pad((string) random_int(10000000000000, 99999999999999), 14, '0', STR_PAD_LEFT),
        'tipo_pessoa' => 'PJ',
    ]);
}

it('grava as respostas de cobrança no lançamento da empresa no curso', function () {
    seedCentroCustoParaCobranca();
    $agenda = criarAgendaCursoComCobranca();
    $empresa = criarEmpresaPjParaCobranca();

    $lancamento = app(GerarLancamentoCursoAction::class)->execute(
        $agenda, $empresa, $empresa, false, '100', null, cobrancaFederal()
    );

    expect($lancamento->fresh())
        ->forma_pagamento->toBe(FormaPagamentoCobranca::BoletoBancario)
        ->exige_pedido_compra->toBeTrue()
        ->entidade_governamental->toBeTrue()
        ->esfera_governamental->toBe(EsferaGovernamental::Federal);
});

it('atualiza as respostas no lançamento compartilhado da empresa e mantém quando não são enviadas', function () {
    seedCentroCustoParaCobranca();
    $agenda = criarAgendaCursoComCobranca();
    $empresa = criarEmpresaPjParaCobranca();

    $lancamento = app(GerarLancamentoCursoAction::class)->execute(
        $agenda, $empresa, $empresa, false, '100', null, cobrancaFederal()
    );

    $mesmoLancamento = app(GerarLancamentoCursoAction::class)->execute(
        $agenda, $empresa, $empresa, false, '100', null, cobrancaNaoGovernamental()
    );

    expect($mesmoLancamento->id)->toBe($lancamento->id);
    expect($mesmoLancamento->fresh())
        ->forma_pagamento->toBe(FormaPagamentoCobranca::DepositoBancario)
        ->exige_pedido_compra->toBeFalse()
        ->entidade_governamental->toBeFalse()
        ->esfera_governamental->toBeNull();

    app(GerarLancamentoCursoAction::class)->execute($agenda, $empresa, $empresa, false, '100');

    expect($mesmoLancamento->fresh()->forma_pagamento)->toBe(FormaPagamentoCobranca::DepositoBancario);
});

it('grava e atualiza a cobrança no lançamento do laboratório do PEP', function () {
    $inscrito = InterlabInscritoFactory::new()->create([
        'valor' => null,
        'lancamento_financeiro_id' => null,
    ]);

    app(GerarLancamentoInterlabAction::class)->execute($inscrito, 1500, cobrancaFederal());

    $inscrito->refresh();
    expect($inscrito->lancamentoFinanceiro)
        ->forma_pagamento->toBe(FormaPagamentoCobranca::BoletoBancario)
        ->esfera_governamental->toBe(EsferaGovernamental::Federal);

    app(GerarLancamentoInterlabAction::class)->execute($inscrito, 2000);

    expect($inscrito->lancamentoFinanceiro->fresh())
        ->valor->toEqual(2000.00)
        ->forma_pagamento->toBe(FormaPagamentoCobranca::BoletoBancario);

    app(GerarLancamentoInterlabAction::class)->atualizarDadosCobranca($inscrito, cobrancaNaoGovernamental());

    expect($inscrito->lancamentoFinanceiro->fresh())
        ->forma_pagamento->toBe(FormaPagamentoCobranca::DepositoBancario)
        ->entidade_governamental->toBeFalse()
        ->esfera_governamental->toBeNull();
});

it('zera a esfera governamental quando a entidade não é governamental', function () {
    $dados = LancamentoFinanceiro::dadosCobrancaDoFormulario([
        'forma_pagamento' => 'deposito_bancario',
        'exige_pedido_compra' => '0',
        'entidade_governamental' => '0',
        'esfera_governamental' => 'federal',
    ]);

    expect($dados)->toBe([
        'forma_pagamento' => 'deposito_bancario',
        'exige_pedido_compra' => false,
        'entidade_governamental' => false,
        'esfera_governamental' => null,
    ]);
});

it('exige as respostas de cobrança para salvar a empresa no PEP', function () {
    Livewire::test(ConfirmaCNPJ::class)
        ->set('empresa', empresaComCobrancaValida())
        ->call('salvar')
        ->assertHasErrors([
            'dadosCobranca.forma_pagamento' => 'required',
            'dadosCobranca.exige_pedido_compra' => 'required',
            'dadosCobranca.entidade_governamental' => 'required',
        ]);

    expect(Pessoa::query()->where('cpf_cnpj', '11222333000181')->exists())->toBeFalse();
});

it('exige a esfera governamental quando a entidade é governamental no PEP', function () {
    Livewire::test(ConfirmaCNPJ::class)
        ->set('empresa', empresaComCobrancaValida())
        ->set('dadosCobranca', [
            'forma_pagamento' => 'boleto_bancario',
            'exige_pedido_compra' => '0',
            'entidade_governamental' => '1',
            'esfera_governamental' => null,
        ])
        ->call('salvar')
        ->assertHasErrors(['dadosCobranca.esfera_governamental' => 'required_if']);
});

it('envia as respostas de cobrança ao salvar a empresa no PEP', function () {
    Livewire::test(ConfirmaCNPJ::class)
        ->set('empresa', empresaComCobrancaValida())
        ->set('dadosCobranca', [
            'forma_pagamento' => 'deposito_bancario',
            'exige_pedido_compra' => '1',
            'entidade_governamental' => '0',
            'esfera_governamental' => 'municipal',
        ])
        ->call('salvar')
        ->assertHasNoErrors()
        ->assertDispatched('empresaSaved', function (string $eventName, array $params) {
            return $params['dados_cobranca'] === [
                'forma_pagamento' => 'deposito_bancario',
                'exige_pedido_compra' => true,
                'entidade_governamental' => false,
                'esfera_governamental' => null,
            ];
        });
});

it('exige as respostas de cobrança para salvar a empresa na inscrição do curso', function () {
    seedCentroCustoParaCobranca();
    $agenda = criarAgendaCursoComCobranca();

    $user = User::factory()->create();
    Pessoa::query()->create([
        'user_id' => $user->id,
        'nome_razao' => 'Cliente PF',
        'cpf_cnpj' => str_pad((string) random_int(10000000000, 99999999999), 11, '0', STR_PAD_LEFT),
        'tipo_pessoa' => 'PF',
        'email' => $user->email,
    ]);

    session()->put('curso', $agenda);

    Livewire::actingAs($user->fresh())
        ->test(ConfirmInscricaoCurso::class)
        ->set('tipoInscricao', 'CNPJ')
        ->set('empresa', empresaComCobrancaValida())
        ->call('salvarEmpresa')
        ->assertHasErrors([
            'dadosCobranca.forma_pagamento' => 'required',
            'dadosCobranca.exige_pedido_compra' => 'required',
            'dadosCobranca.entidade_governamental' => 'required',
        ]);
});

it('exibe as respostas de cobrança no cartão do lançamento financeiro', function () {
    $user = User::factory()->create();
    $permission = Permission::withoutEvents(fn (): Permission => Permission::query()->firstOrCreate(['permission' => 'funcionario']));
    $user->permissions()->syncWithoutDetaching([$permission->id]);

    $lancamento = LancamentoFinanceiroFactory::new()->create([
        'forma_pagamento' => 'boleto_bancario',
        'exige_pedido_compra' => true,
        'entidade_governamental' => true,
        'esfera_governamental' => 'estadual',
    ]);

    $this->actingAs($user)
        ->get(route('lancamento-financeiro-insert', ['lancamento' => $lancamento->uid]))
        ->assertOk()
        ->assertSee('Forma de pagamento: Boleto bancário', false)
        ->assertSee('Necessário envio de pedido/ordem de compra ou empenho: Sim', false)
        ->assertSee('É entidade governamental? Sim - Estadual', false);
});
