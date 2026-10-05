<?php

use App\Actions\Interlab\CriarInterlabLotePostagemAction;
use App\Actions\Interlab\DestinatarioInterlabSnapshot;
use App\Enums\InterlabLotePostagemItemStatus;
use App\Enums\InterlabLotePostagemStatus;
use App\Jobs\Interlab\ClassificarServicoLotePostagemJob;
use App\Livewire\Interlab\GerarLotePostagem;
use App\Models\Endereco;
use App\Models\InterlabInscrito;
use App\Models\InterlabLaboratorio;
use Database\Factories\AgendaInterlabFactory;
use Database\Factories\InterlabInscritoFactory;
use Database\Factories\InterlabLotePostagemFactory;
use Database\Factories\PessoaFactory;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

beforeEach(function () {
    Queue::fake([ClassificarServicoLotePostagemJob::class]);
});

test('enfileira a classificacao depois de persistir o lote', function () {
    $inscrito = inscritoParaLotePostagem();

    $lote = app(CriarInterlabLotePostagemAction::class)->execute(
        $inscrito->agendaInterlab,
        [$inscrito->id],
        parametrosDoLotePostagem(),
    );

    Queue::assertPushed(
        ClassificarServicoLotePostagemJob::class,
        fn (ClassificarServicoLotePostagemJob $job): bool => $job->lote->is($lote),
    );
});

test('persiste o lote e um item por laboratorio com nu requisicao unico', function () {
    $primeiro = inscritoParaLotePostagem(['nome' => 'Laboratório Alfa', 'endereco' => 'Rua das Flores, 10']);
    $segundo = inscritoParaLotePostagem([
        'agenda' => $primeiro->agendaInterlab,
        'nome' => 'Laboratório Beta',
        'endereco' => 'Avenida Brasil, 20',
    ]);

    $lote = app(CriarInterlabLotePostagemAction::class)->execute(
        $primeiro->agendaInterlab,
        [$primeiro->id, $segundo->id],
        parametrosDoLotePostagem(),
    );

    expect($lote->nome)->toBe('Lote setembro')
        ->and($lote->codigo_formato->value)->toBe('2')
        ->and($lote->altura)->toBe('10')
        ->and($lote->largura)->toBe('15')
        ->and($lote->comprimento)->toBe('20')
        ->and($lote->diametro)->toBeNull()
        ->and($lote->peso_gramas)->toBe(500)
        ->and($lote->declaracao_conteudo)->toBe(parametrosDoLotePostagem()['declaracao_conteudo'])
        ->and($lote->status)->toBe(InterlabLotePostagemStatus::ClassificandoServico)
        ->and($lote->ultima_progressao_em)->not->toBeNull()
        ->and($lote->itens)->toHaveCount(2);

    $requisicoes = $lote->itens->pluck('nu_requisicao');

    expect($requisicoes->unique())->toHaveCount(2)
        ->and($requisicoes->every(fn (string $requisicao): bool => Str::isUuid($requisicao)))->toBeTrue()
        ->and($lote->itens->pluck('status')->unique()->all())->toBe([
            InterlabLotePostagemItemStatus::AguardandoClassificacao,
        ]);
});

test('grava o snapshot nacional do destinatario no formato da api', function () {
    $inscrito = inscritoParaLotePostagem([
        'nome' => 'Laboratório Alfa',
        'endereco' => 'Praça da Sé, 1',
        'bairro' => 'Sé',
        'cidade' => 'São Paulo',
        'uf' => 'SP',
        'cep' => '01001000',
    ]);

    $lote = app(CriarInterlabLotePostagemAction::class)->execute(
        $inscrito->agendaInterlab,
        [$inscrito->id],
        parametrosDoLotePostagem(),
    );

    expect($lote->itens->sole()->destinatario)->toBe([
        'nome' => 'Laboratório Alfa',
        'endereco' => [
            'cep' => '01001000',
            'logradouro' => 'Praça da Sé',
            'numero' => '1',
            'bairro' => 'Sé',
            'cidade' => 'São Paulo',
            'uf' => 'SP',
            'regiao' => '',
        ],
    ]);
});

test('usa o numero gravado no complemento quando o endereco nao tem numero', function () {
    $inscrito = inscritoParaLotePostagem([
        'nome' => 'Instituto de Qualidade Ambiental Ltda.',
        'endereco' => 'Rua João Neves da Fontoura',
        'complemento' => '117',
    ]);

    $lote = app(CriarInterlabLotePostagemAction::class)->execute(
        $inscrito->agendaInterlab,
        [$inscrito->id],
        parametrosDoLotePostagem(),
    );

    expect($lote->itens->sole()->destinatario['endereco'])->toBe([
        'cep' => '01001000',
        'logradouro' => 'Rua João Neves da Fontoura',
        'numero' => '117',
        'bairro' => 'Sé',
        'cidade' => 'São Paulo',
        'uf' => 'SP',
        'regiao' => '',
    ]);
});

test('separa o numero do cadastro antes de validar', function (array $cadastro, array $esperado) {
    $inscrito = inscritoParaLotePostagem($cadastro);

    $lote = app(CriarInterlabLotePostagemAction::class)->execute(
        $inscrito->agendaInterlab,
        [$inscrito->id],
        parametrosDoLotePostagem(),
    );

    expect($lote->itens->sole()->destinatario['endereco'])->toMatchArray($esperado);
})->with([
    'prefixo no complemento' => [
        ['endereco' => 'Rua Pascoal Meller', 'complemento' => 'n. 73'],
        ['logradouro' => 'Rua Pascoal Meller', 'numero' => '73'],
    ],
    'numero no fim do logradouro' => [
        ['endereco' => 'Barão do Guaíba 781', 'complemento' => 'DMAE'],
        ['logradouro' => 'Barão do Guaíba', 'numero' => '781', 'complemento' => 'DMAE'],
    ],
    'numero no endereco e complemento real' => [
        ['endereco' => 'Avenida Senador Alberto Pasqualini, 668', 'complemento' => '201'],
        ['logradouro' => 'Avenida Senador Alberto Pasqualini', 'numero' => '668', 'complemento' => '201'],
    ],
]);

test('normaliza cep sem hifen e separa o numero antes de validar', function () {
    $inscrito = inscritoParaLotePostagem([
        'endereco' => 'Rua das Flores, 100',
        'cep' => '92990000',
    ]);

    Endereco::query()->whereKey($inscrito->laboratorio->endereco_id)->update([
        'cep' => '92990-000',
    ]);

    $lote = app(CriarInterlabLotePostagemAction::class)->execute(
        $inscrito->agendaInterlab,
        [$inscrito->id],
        parametrosDoLotePostagem(),
    );

    expect($lote->itens->sole()->destinatario['endereco'])->toMatchArray([
        'cep' => '92990000',
        'logradouro' => 'Rua das Flores',
        'numero' => '100',
    ]);
});

test('grava o nome do laboratorio e ignora o nome da empresa', function () {
    $inscrito = inscritoParaLotePostagem(['nome' => 'DILAB - FEPAM']);
    $inscrito->laboratorio->empresa()->update([
        'nome_razao' => 'FEPAM - FUNDAÇÃO ESTADUAL DE PROTEÇÃO AMBIENTAL HENRIQUE LUIZ ROESSLER',
    ]);

    $lote = app(CriarInterlabLotePostagemAction::class)->execute(
        $inscrito->agendaInterlab,
        [$inscrito->id],
        parametrosDoLotePostagem(),
    );

    expect($lote->itens->sole()->destinatario['nome'])->toBe('DILAB - FEPAM');
});

test('aceita nome de laboratorio acima de 50 e abaixo de 255 caracteres', function () {
    $nome = 'Pró Ambiente Análises Químicas e Toxicológicas LTDA';
    $inscrito = inscritoParaLotePostagem(['nome' => $nome]);

    $lote = app(CriarInterlabLotePostagemAction::class)->execute(
        $inscrito->agendaInterlab,
        [$inscrito->id],
        parametrosDoLotePostagem(),
    );

    expect($lote->itens->sole()->destinatario['nome'])->toBe($nome);
});

test('recusa nome de laboratorio fora de 3 a 255 caracteres', function (string $nome) {
    $inscrito = inscritoParaLotePostagem(['nome' => $nome]);

    expect(fn () => app(CriarInterlabLotePostagemAction::class)->execute(
        $inscrito->agendaInterlab,
        [$inscrito->id],
        parametrosDoLotePostagem(),
    ))->toThrow(ValidationException::class, 'deve ter entre 3 e 255 caracteres');

    $this->assertDatabaseCount('interlab_lote_postagens', 0);
})->with([
    'curto' => 'AB',
    'longo' => str_repeat('A', 256),
]);

test('trunca o nome em 50 caracteres so no payload da pre postagem', function () {
    $nome = str_repeat('Á', 60);
    $inscrito = inscritoParaLotePostagem(['nome' => $nome]);

    $lote = app(CriarInterlabLotePostagemAction::class)->execute(
        $inscrito->agendaInterlab,
        [$inscrito->id],
        parametrosDoLotePostagem(),
    );
    $item = $lote->itens->sole();
    $payload = app(DestinatarioInterlabSnapshot::class)->paraPrePostagem($item->destinatario);

    expect($item->destinatario['nome'])->toBe($nome)
        ->and(Str::length($payload['nome']))->toBe(50)
        ->and($payload['nome'])->toBe(str_repeat('Á', 50))
        ->and($item->fresh()->destinatario['nome'])->toBe($nome);
});

test('recusa endereco sem numero e nao grava o lote', function () {
    $inscrito = inscritoParaLotePostagem([
        'nome' => 'Laboratório Sem Número',
        'endereco' => 'Praça da Sé',
    ]);

    expect(fn () => app(CriarInterlabLotePostagemAction::class)->execute(
        $inscrito->agendaInterlab,
        [$inscrito->id],
        parametrosDoLotePostagem(),
    ))->toThrow(ValidationException::class, 'Informe o número do endereço do laboratório Laboratório Sem Número.');

    $this->assertDatabaseCount('interlab_lote_postagens', 0);
});

test('recusa destinatario acima do limite da api e nao grava o lote', function () {
    $inscrito = inscritoParaLotePostagem([
        'nome' => 'Laboratório Endereço Longo',
        'endereco' => str_repeat('A', 51).', 1',
        'bairro' => str_repeat('B', 31),
    ]);

    $exception = null;

    try {
        app(CriarInterlabLotePostagemAction::class)->execute(
            $inscrito->agendaInterlab,
            [$inscrito->id],
            parametrosDoLotePostagem(),
        );
    } catch (ValidationException $caught) {
        $exception = $caught;
    }

    expect($exception)->toBeInstanceOf(ValidationException::class)
        ->and($exception->errors()['inscritosSelecionados'])->toContain(
            'O logradouro do laboratório Laboratório Endereço Longo deve ter no máximo 50 caracteres.',
            'O bairro do laboratório Laboratório Endereço Longo deve ter no máximo 30 caracteres.',
        );

    $this->assertDatabaseCount('interlab_lote_postagens', 0);
    $this->assertDatabaseCount('interlab_lote_postagem_itens', 0);
});

test('recusa outro lote enquanto a agenda tem postagem em processamento', function () {
    $inscrito = inscritoParaLotePostagem();
    InterlabLotePostagemFactory::new()->create([
        'agenda_interlab_id' => $inscrito->agenda_interlab_id,
        'status' => InterlabLotePostagemStatus::ClassificandoServico,
    ]);

    expect(fn () => app(CriarInterlabLotePostagemAction::class)->execute(
        $inscrito->agendaInterlab,
        [$inscrito->id],
        parametrosDoLotePostagem(),
    ))->toThrow(ValidationException::class, 'Já existe um lote de postagem em processamento nesta agenda.');

    $this->assertDatabaseCount('interlab_lote_postagens', 1);
});

test('permite novo lote quando o anterior nao esta em processamento', function (InterlabLotePostagemStatus $status) {
    $inscrito = inscritoParaLotePostagem();
    InterlabLotePostagemFactory::new()->create([
        'agenda_interlab_id' => $inscrito->agenda_interlab_id,
        'status' => $status,
    ]);

    app(CriarInterlabLotePostagemAction::class)->execute(
        $inscrito->agendaInterlab,
        [$inscrito->id],
        parametrosDoLotePostagem(),
    );

    $this->assertDatabaseCount('interlab_lote_postagens', 2);
    $this->assertDatabaseHas('interlab_lote_postagens', [
        'agenda_interlab_id' => $inscrito->agenda_interlab_id,
        'status' => InterlabLotePostagemStatus::ClassificandoServico->value,
    ]);
})->with([
    'concluido' => InterlabLotePostagemStatus::Concluido,
    'erro' => InterlabLotePostagemStatus::Erro,
]);

test('recusa inscricao de outra agenda ao persistir o lote', function () {
    $inscrito = inscritoParaLotePostagem();
    $outraAgenda = inscritoParaLotePostagem();

    expect(fn () => app(CriarInterlabLotePostagemAction::class)->execute(
        $inscrito->agendaInterlab,
        [$outraAgenda->id],
        parametrosDoLotePostagem(),
    ))->toThrow(ValidationException::class, 'não pertencem a esta agenda');

    $this->assertDatabaseCount('interlab_lote_postagens', 0);
});

test('gerar postagem persiste o lote e bloqueia nova geracao', function () {
    $inscrito = inscritoParaLotePostagem([
        'nome' => 'Laboratório Alfa',
        'endereco' => 'Praça da Sé, 1',
    ]);

    Livewire::test(GerarLotePostagem::class, ['agenda' => $inscrito->agendaInterlab])
        ->set('inscritosSelecionados', [$inscrito->id])
        ->set('form.nome', 'Lote setembro')
        ->set('form.altura', '10')
        ->set('form.largura', '15')
        ->set('form.comprimento', '20')
        ->set('form.pesoGramas', '500')
        ->set('form.itensDeclaracao', [[
            'conteudo' => 'Amostra para ensaio interlaboratorial',
            'quantidade' => '1',
            'valor_unitario' => '50,00',
        ]])
        ->call('gerarPostagem')
        ->assertHasNoErrors()
        ->assertDispatched('show-success-alert', message: 'Lote de postagem criado.')
        ->assertSee('Lote setembro')
        ->assertSee('Classificando serviço')
        ->assertDontSee('>Gerar postagem<', false);

    $this->assertDatabaseHas('interlab_lote_postagens', [
        'agenda_interlab_id' => $inscrito->agenda_interlab_id,
        'nome' => 'Lote setembro',
        'peso_gramas' => 500,
        'status' => InterlabLotePostagemStatus::ClassificandoServico->value,
    ]);
    $this->assertDatabaseHas('interlab_lote_postagem_itens', [
        'interlab_inscrito_id' => $inscrito->id,
        'interlab_laboratorio_id' => $inscrito->laboratorio_id,
        'status' => InterlabLotePostagemItemStatus::AguardandoClassificacao->value,
    ]);
});

/**
 * @param  array<string, mixed>  $overrides
 */
function inscritoParaLotePostagem(array $overrides = []): InterlabInscrito
{
    $agenda = $overrides['agenda'] ?? AgendaInterlabFactory::new()->create();
    $empresa = PessoaFactory::new()->create(['tipo_pessoa' => 'PJ']);

    $endereco = Endereco::query()->create([
        'pessoa_id' => $empresa->id,
        'cep' => $overrides['cep'] ?? '01001000',
        'endereco' => $overrides['endereco'] ?? 'Praça da Sé, 100',
        'complemento' => $overrides['complemento'] ?? null,
        'bairro' => $overrides['bairro'] ?? 'Sé',
        'cidade' => $overrides['cidade'] ?? 'São Paulo',
        'uf' => $overrides['uf'] ?? 'SP',
    ]);

    $laboratorio = InterlabLaboratorio::query()->create([
        'empresa_id' => $empresa->id,
        'endereco_id' => $endereco->id,
        'nome' => $overrides['nome'] ?? 'Laboratório Teste',
    ]);

    return InterlabInscritoFactory::new()->create([
        'agenda_interlab_id' => $agenda->id,
        'empresa_id' => $empresa->id,
        'laboratorio_id' => $laboratorio->id,
    ])->load('agendaInterlab');
}

/**
 * @return array{
 *     nome: string,
 *     codigo_formato: string,
 *     altura: string,
 *     largura: string,
 *     comprimento: string,
 *     diametro: null,
 *     peso_gramas: int,
 *     declaracao_conteudo: list<array{conteudo: string, quantidade: string, valor: string}>
 * }
 */
function parametrosDoLotePostagem(): array
{
    return [
        'nome' => 'Lote setembro',
        'codigo_formato' => '2',
        'altura' => '10',
        'largura' => '15',
        'comprimento' => '20',
        'diametro' => null,
        'peso_gramas' => 500,
        'declaracao_conteudo' => [[
            'conteudo' => 'Amostra para ensaio interlaboratorial',
            'quantidade' => '1',
            'valor' => '50.00',
        ]],
    ];
}
