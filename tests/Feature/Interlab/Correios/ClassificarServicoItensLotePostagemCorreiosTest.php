<?php

use App\Actions\Interlab\ClassificarServicoItensLotePostagemCorreiosAction;
use App\Enums\InterlabLotePostagemItemStatus;
use App\Enums\InterlabLotePostagemStatus;
use App\Jobs\Interlab\ClassificarServicoLotePostagemJob;
use App\Livewire\Interlab\GerarLotePostagem;
use App\Mail\InterlabLotePostagemErroMail;
use App\Models\InterlabLotePostagem;
use App\Models\InterlabLotePostagemItem;
use Database\Factories\InterlabLotePostagemFactory;
use Database\Factories\InterlabLotePostagemItemFactory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

test('classifica todos os destinos como sedex 12 e mantem o lote na etapa', function () {
    configurarClassificacaoCorreios();
    $this->freezeTime();

    $lote = loteComItensPendentes(2);
    $lote->update(['ultima_progressao_em' => now()->subMinute()]);

    Http::fake(fn (Request $request) => respostaPrazoCorreios($request, fn (array $parametro): array => [
        'nuRequisicao' => $parametro['nuRequisicao'],
        'prazoEntrega' => 1,
    ]));

    $resultado = app(ClassificarServicoItensLotePostagemCorreiosAction::class)->execute($lote);

    expect($resultado->estaConcluida())->toBeTrue();

    $lote->refresh();

    expect($lote->status)->toBe(InterlabLotePostagemStatus::ClassificandoServico)
        ->and($lote->ultima_progressao_em?->toDateTimeString())->toBe(now()->toDateTimeString());

    $lote->itens->each(function (InterlabLotePostagemItem $item): void {
        $item->refresh();

        expect($item->status)->toBe(InterlabLotePostagemItemStatus::Classificado)
            ->and($item->codigo_servico)->toBe('03140')
            ->and($item->motivo_fallback_servico)->toBeNull()
            ->and($item->consulta_prazo_em?->toDateTimeString())->toBe(now()->toDateTimeString())
            ->and($item->consulta_prazo_request)->toMatchArray([
                'coProduto' => '03140',
                'nuRequisicao' => $item->nu_requisicao,
                'cepOrigem' => '90010000',
                'cepDestino' => '01001000',
                'dataPostagem' => now('America/Sao_Paulo')->toDateString(),
            ])
            ->and($item->consulta_prazo_response['prazoEntrega'])->toBe(1);
    });
});

test('correlaciona 206 pelo nu requisicao e usa sedex no prz-008', function () {
    configurarClassificacaoCorreios();

    $lote = loteComItensPendentes(2);
    $itens = $lote->itens()->orderBy('id')->get();

    Http::fake(function (Request $request) use ($itens) {
        if (str_contains($request->url(), '/token/')) {
            return respostaTokenCorreios();
        }

        return Http::response([
            [
                'nuRequisicao' => $itens[1]->nu_requisicao,
                'txErro' => 'PRZ-008: Serviço indisponível para o trecho informado',
            ],
            [
                'nuRequisicao' => $itens[0]->nu_requisicao,
                'prazoEntrega' => 3,
            ],
        ], 206);
    });

    app(ClassificarServicoItensLotePostagemCorreiosAction::class)->execute($lote);

    expect($itens[0]->fresh()->codigo_servico)->toBe('03140')
        ->and($itens[0]->fresh()->motivo_fallback_servico)->toBeNull()
        ->and($itens[1]->fresh()->codigo_servico)->toBe('03220')
        ->and($itens[1]->fresh()->motivo_fallback_servico)->toBe('PRZ-008')
        ->and($itens[1]->fresh()->status)->toBe(InterlabLotePostagemItemStatus::Classificado);
});

test('http 400 com prz-008 por item classifica sedex e nao encerra o lote', function () {
    configurarClassificacaoCorreios();
    Mail::fake();

    $lote = loteComItensPendentes();
    $nuRequisicao = $lote->itens->first()->nu_requisicao;

    Http::fake([
        'https://api.correios.com.br/token/v1/autentica/cartaopostagem' => respostaTokenCorreios(),
        'https://api.correios.com.br/prazo/v1/nacional' => Http::response([
            [
                'coProduto' => '03140',
                'nuRequisicao' => $nuRequisicao,
                'txErro' => 'PRZ-008: Serviço indisponível para o trecho informado',
            ],
        ], 400),
    ]);

    $resultado = app(ClassificarServicoItensLotePostagemCorreiosAction::class)->execute($lote);
    $item = $lote->itens->first()->fresh();

    expect($resultado->estaConcluida())->toBeTrue()
        ->and($lote->fresh()->status)->toBe(InterlabLotePostagemStatus::ClassificandoServico)
        ->and($item->status)->toBe(InterlabLotePostagemItemStatus::Classificado)
        ->and($item->codigo_servico)->toBe('03220')
        ->and($item->motivo_fallback_servico)->toBe('PRZ-008');

    Mail::assertNothingQueued();
});

test('consulta em grupos de ate 5 e nao reenvia item ja classificado', function () {
    configurarClassificacaoCorreios();

    $lote = loteComItensPendentes(6);

    Http::fake(fn (Request $request) => respostaPrazoCorreios($request, fn (array $parametro): array => [
        'nuRequisicao' => $parametro['nuRequisicao'],
        'prazoEntrega' => 1,
    ]));

    app(ClassificarServicoItensLotePostagemCorreiosAction::class)->execute($lote);

    $consultas = consultasPrazoEnviadas();

    expect($consultas)->toHaveCount(2)
        ->and($consultas[0]->data()['parametrosPrazo'])->toHaveCount(5)
        ->and($consultas[1]->data()['parametrosPrazo'])->toHaveCount(1);

    $primeira = collect($consultas[0]->data()['parametrosPrazo'])->pluck('nuRequisicao');
    $segunda = collect($consultas[1]->data()['parametrosPrazo'])->pluck('nuRequisicao');

    expect($primeira->intersect($segunda))->toBeEmpty()
        ->and($lote->itens()->where('status', InterlabLotePostagemItemStatus::Classificado)->count())->toBe(6);
});

test('timeout mantem o item pendente sem usar sedex', function () {
    configurarClassificacaoCorreios();
    $this->freezeTime();

    $lote = loteComItensPendentes();
    $momento = now()->subMinutes(3);
    $lote->update(['ultima_progressao_em' => $momento]);
    $nuRequisicao = $lote->itens->first()->nu_requisicao;

    Http::fake([
        'https://api.correios.com.br/token/v1/autentica/cartaopostagem' => respostaTokenCorreios(),
        'https://api.correios.com.br/prazo/v1/nacional' => Http::failedConnection(),
    ]);

    $resultado = app(ClassificarServicoItensLotePostagemCorreiosAction::class)->execute($lote, 2);

    $item = $lote->itens->first()->fresh();

    expect($resultado->deveRetentar())->toBeTrue()
        ->and($resultado->atrasoSegundos)->toBe(2)
        ->and($item->status)->toBe(InterlabLotePostagemItemStatus::AguardandoClassificacao)
        ->and($item->codigo_servico)->toBeNull()
        ->and($item->nu_requisicao)->toBe($nuRequisicao)
        ->and($item->consulta_prazo_request['nuRequisicao'])->toBe($nuRequisicao)
        ->and($lote->fresh()->ultima_progressao_em?->toDateTimeString())->toBe($momento->toDateTimeString());
});

test('falha transitoria de prazo nao classifica e respeita retry after', function (int $status, int $tentativa, int $atraso, array $headers) {
    configurarClassificacaoCorreios();

    $lote = loteComItensPendentes();

    Http::fake([
        'https://api.correios.com.br/token/v1/autentica/cartaopostagem' => respostaTokenCorreios(),
        'https://api.correios.com.br/prazo/v1/nacional' => Http::response(['msgs' => ['indisponivel']], $status, $headers),
    ]);

    $resultado = app(ClassificarServicoItensLotePostagemCorreiosAction::class)->execute($lote, $tentativa);
    $item = $lote->itens->first()->fresh();

    expect($resultado->deveRetentar())->toBeTrue()
        ->and($resultado->atrasoSegundos)->toBe($atraso)
        ->and($item->status)->toBe(InterlabLotePostagemItemStatus::AguardandoClassificacao)
        ->and($item->codigo_servico)->toBeNull();
})->with([
    '429' => [429, 1, 1, []],
    '429 com retry after' => [429, 1, 30, ['Retry-After' => '30']],
    '500' => [500, 3, 4, []],
]);

test('job libera a retentativa pelo atraso calculado', function () {
    configurarClassificacaoCorreios();

    $lote = loteComItensPendentes();

    Http::fake([
        'https://api.correios.com.br/token/v1/autentica/cartaopostagem' => respostaTokenCorreios(),
        'https://api.correios.com.br/prazo/v1/nacional' => Http::response([], 429, ['Retry-After' => '30']),
    ]);

    $job = (new ClassificarServicoLotePostagemJob($lote))->withFakeQueueInteractions();
    $job->handle(app(ClassificarServicoItensLotePostagemCorreiosAction::class));

    $job->assertReleased(30);
});

test('retentativa reutiliza o mesmo nu requisicao', function () {
    configurarClassificacaoCorreios();

    $lote = loteComItensPendentes();
    $nuRequisicao = $lote->itens->first()->nu_requisicao;
    $chamadas = 0;

    Http::fake(function (Request $request) use (&$chamadas, $nuRequisicao) {
        if (str_contains($request->url(), '/token/')) {
            return respostaTokenCorreios();
        }

        $chamadas++;

        if ($chamadas === 1) {
            return Http::response([], 503);
        }

        return Http::response([[
            'nuRequisicao' => $nuRequisicao,
            'prazoEntrega' => 1,
        ]], 200);
    });

    $action = app(ClassificarServicoItensLotePostagemCorreiosAction::class);
    $action->execute($lote, 1);
    $action->execute($lote->fresh(), 2);

    $consultas = consultasPrazoEnviadas();

    expect($consultas)->toHaveCount(2)
        ->and($consultas[0]->data()['parametrosPrazo'][0]['nuRequisicao'])->toBe($nuRequisicao)
        ->and($consultas[1]->data()['parametrosPrazo'][0]['nuRequisicao'])->toBe($nuRequisicao)
        ->and($lote->itens->first()->fresh()->nu_requisicao)->toBe($nuRequisicao)
        ->and($lote->itens->first()->fresh()->codigo_servico)->toBe('03140');
});

test('http definitivo encerra o lote sem renovar token nem usar sedex', function (int $status) {
    configurarClassificacaoCorreios();
    Mail::fake();

    $lote = loteComItensPendentes();
    $classificado = InterlabLotePostagemItemFactory::new()->classificadoSedex12()->create([
        'interlab_lote_postagem_id' => $lote->id,
    ]);

    Http::fake([
        'https://api.correios.com.br/token/v1/autentica/cartaopostagem' => respostaTokenCorreios(),
        'https://api.correios.com.br/prazo/v1/nacional' => Http::response(['msgs' => ['nao autorizado']], $status),
    ]);

    $resultado = app(ClassificarServicoItensLotePostagemCorreiosAction::class)->execute($lote);
    $lote->refresh();

    expect($resultado->estaFalhou())->toBeTrue()
        ->and($lote->status)->toBe(InterlabLotePostagemStatus::Erro)
        ->and($lote->etapa_erro)->toBe(InterlabLotePostagemStatus::ClassificandoServico->value)
        ->and($classificado->fresh()->status)->toBe(InterlabLotePostagemItemStatus::Classificado)
        ->and($classificado->fresh()->codigo_servico)->toBe('03140')
        ->and($lote->itens()->where('status', InterlabLotePostagemItemStatus::Erro)->count())->toBe(1);

    $tokens = Http::recorded()->filter(
        fn (array $par): bool => str_contains($par[0]->url(), '/token/v1/autentica/cartaopostagem'),
    );

    expect($tokens)->toHaveCount(1);

    Mail::assertQueued(InterlabLotePostagemErroMail::class, function (InterlabLotePostagemErroMail $mail) use ($lote): bool {
        return $mail->hasTo('sistema@redemetrologica.com.br')
            && $mail->lote->is($lote);
    });
})->with([400, 401, 403]);

test('vinte minutos sem progresso encerra o lote sem consultar os correios', function () {
    configurarClassificacaoCorreios();
    Mail::fake();
    $this->freezeTime();
    Http::preventStrayRequests();
    Http::fake();

    $lote = loteComItensPendentes();
    $lote->update(['ultima_progressao_em' => now()->subMinutes(20)]);

    $resultado = app(ClassificarServicoItensLotePostagemCorreiosAction::class)->execute($lote);

    expect($resultado->estaFalhou())->toBeTrue()
        ->and($lote->fresh()->status)->toBe(InterlabLotePostagemStatus::Erro);

    Http::assertNothingSent();
    Mail::assertQueued(InterlabLotePostagemErroMail::class);
});

test('txErro diferente de prz-008 encerra o lote e preserva item ja classificado', function () {
    configurarClassificacaoCorreios();
    Mail::fake();

    $lote = loteComItensPendentes(2);
    $itens = $lote->itens()->orderBy('id')->get();

    Http::fake(function (Request $request) use ($itens) {
        if (str_contains($request->url(), '/token/')) {
            return respostaTokenCorreios();
        }

        return Http::response([
            [
                'nuRequisicao' => $itens[0]->nu_requisicao,
                'prazoEntrega' => 1,
            ],
            [
                'nuRequisicao' => $itens[1]->nu_requisicao,
                'txErro' => 'PRZ-001',
            ],
        ], 200);
    });

    app(ClassificarServicoItensLotePostagemCorreiosAction::class)->execute($lote);

    expect($lote->fresh()->status)->toBe(InterlabLotePostagemStatus::Erro)
        ->and($itens[0]->fresh()->status)->toBe(InterlabLotePostagemItemStatus::Classificado)
        ->and($itens[0]->fresh()->codigo_servico)->toBe('03140')
        ->and($itens[1]->fresh()->status)->toBe(InterlabLotePostagemItemStatus::Erro)
        ->and($itens[1]->fresh()->codigo_servico)->toBeNull();
});

test('exibe o status visual de cada item enquanto o lote classifica', function () {
    $lote = InterlabLotePostagemFactory::new()->create();
    $snapshot = fn (string $nome): array => [
        'nome' => $nome,
        'endereco' => [
            'cep' => '01001000',
            'logradouro' => 'Praça da Sé',
            'numero' => '1',
            'bairro' => 'Sé',
            'cidade' => 'São Paulo',
            'uf' => 'SP',
            'regiao' => '',
        ],
    ];

    InterlabLotePostagemItemFactory::new()->create([
        'interlab_lote_postagem_id' => $lote->id,
        'destinatario' => $snapshot('Lab Aguardando'),
    ]);
    InterlabLotePostagemItemFactory::new()->create([
        'interlab_lote_postagem_id' => $lote->id,
        'destinatario' => $snapshot('Lab Consultando'),
        'consulta_prazo_request' => ['coProduto' => '03140'],
        'consulta_prazo_response' => null,
    ]);
    InterlabLotePostagemItemFactory::new()->create([
        'interlab_lote_postagem_id' => $lote->id,
        'destinatario' => $snapshot('Lab Retentativa'),
        'consulta_prazo_request' => ['coProduto' => '03140'],
        'consulta_prazo_response' => [
            'tentativa' => 2,
            'proxima_tentativa_em' => '2026-09-14T16:30:00-03:00',
        ],
    ]);
    InterlabLotePostagemItemFactory::new()->classificadoSedex12()->create([
        'interlab_lote_postagem_id' => $lote->id,
        'destinatario' => $snapshot('Lab Sedex 12'),
    ]);
    InterlabLotePostagemItemFactory::new()->classificadoSedex()->create([
        'interlab_lote_postagem_id' => $lote->id,
        'destinatario' => $snapshot('Lab Sedex'),
    ]);

    Livewire::test(GerarLotePostagem::class, ['agenda' => $lote->agendaInterlab])
        ->assertSee('Aguardando consulta de prazo')
        ->assertSee('Consultando prazo')
        ->assertSee('Aguardando nova tentativa')
        ->assertSee('tentativa 2')
        ->assertSee('próxima tentativa em 14/09/2026 16:30')
        ->assertSee('Prazo validado — SEDEX 12')
        ->assertSee('Prazo validado — SEDEX')
        ->assertSeeHtml('wire:poll.5s')
        ->assertDontSee('Gerar postagem');
});

test('lote com erro mostra intervencao e libera novo lote', function () {
    $lote = InterlabLotePostagemFactory::new()->create([
        'status' => InterlabLotePostagemStatus::Erro,
        'etapa_erro' => InterlabLotePostagemStatus::ClassificandoServico->value,
        'erro_mensagem' => 'HTTP 400 na consulta de prazo',
    ]);
    criarInscritoParaPostagem(['agenda' => $lote->agendaInterlab]);

    InterlabLotePostagemItemFactory::new()->create([
        'interlab_lote_postagem_id' => $lote->id,
        'status' => InterlabLotePostagemItemStatus::Erro,
        'destinatario' => [
            'nome' => 'Lab Intervenção',
            'endereco' => [
                'cep' => '01001000',
                'logradouro' => 'Praça da Sé',
                'numero' => '1',
                'bairro' => 'Sé',
                'cidade' => 'São Paulo',
                'uf' => 'SP',
                'regiao' => '',
            ],
        ],
    ]);

    Livewire::test(GerarLotePostagem::class, ['agenda' => $lote->agendaInterlab])
        ->assertSee('Requer intervenção')
        ->assertSee('HTTP 400 na consulta de prazo')
        ->assertSee('classificando_servico')
        ->assertSee('Gerar postagem')
        ->assertSee('Limpar status')
        ->assertDontSeeHtml('wire:poll.5s');
});

function loteComItensPendentes(int $quantidade = 1): InterlabLotePostagem
{
    $lote = InterlabLotePostagemFactory::new()->create([
        'ultima_progressao_em' => now(),
    ]);

    InterlabLotePostagemItemFactory::new()->count($quantidade)->create([
        'interlab_lote_postagem_id' => $lote->id,
    ]);

    return $lote->load('itens');
}
