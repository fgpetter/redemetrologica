<?php

use App\Actions\Interlab\CriarPrePostagemItemLoteCorreiosAction;
use App\Enums\CorreiosPrePostagemStatus;
use App\Enums\InterlabLotePostagemItemStatus;
use App\Enums\InterlabLotePostagemStatus;
use App\Mail\InterlabLotePostagemErroMail;
use Illuminate\Http\Client\Request;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Sleep;
use Illuminate\Support\Str;

beforeEach(function () {
    configurarPrePostagemCorreios();
    Mail::fake();
});

test('cria pre postagem com status atual 2 e trunca o nome no payload', function () {
    $eventos = [];
    Event::listen(MessageLogged::class, function (MessageLogged $event) use (&$eventos): void {
        $eventos[] = $event;
    });

    $lote = loteComItensClassificados(1);
    $item = $lote->itens->first();
    $nomeLongo = Str::repeat('A', 60);
    $item->update([
        'destinatario' => array_merge($item->destinatario, ['nome' => $nomeLongo]),
    ]);

    Http::fake([
        '*/token/v1/autentica/cartaopostagem' => respostaTokenCorreios(),
        '*/prepostagem/v1/prepostagens' => Http::response([
            'id' => 'PP-123',
            'codigoObjeto' => 'DG123456789BR',
            'statusAtual' => 2,
            'descStatusAtual' => 'Pré-postado',
        ], 200),
    ]);

    $resultado = app(CriarPrePostagemItemLoteCorreiosAction::class)->execute($item->fresh());

    expect($resultado->estaConcluida())->toBeTrue();

    $item->refresh();

    expect($item->status)->toBe(InterlabLotePostagemItemStatus::Prepostado)
        ->and($item->id_prepostagem)->toBe('PP-123')
        ->and($item->codigo_objeto)->toBe('DG123456789BR')
        ->and($item->status_atual)->toBe(CorreiosPrePostagemStatus::Prepostado)
        ->and($item->destinatario['nome'])->toBe($nomeLongo);

    Http::assertSent(function (Request $request): bool {
        if (! str_contains($request->url(), '/prepostagem/v1/prepostagens') || str_contains($request->url(), 'rotulo')) {
            return false;
        }

        $data = $request->data();

        return ($data['destinatario']['nome'] ?? null) === Str::repeat('A', 50)
            && ($data['codigoServico'] ?? null) === '03140'
            && ($data['pesoInformado'] ?? null) === '500'
            && ($data['codigoFormatoObjetoInformado'] ?? null) === '2'
            && ($data['alturaInformada'] ?? null) === '10'
            && ($data['emiteDCe'] ?? null) === 'S'
            && ($data['itensDeclaracaoConteudo'][0]['conteudo'] ?? null) === 'Amostra para ensaio interlaboratorial'
            && ($data['cienteObjetoNaoProibido'] ?? null) === '1'
            && ($data['destinatario']['endereco']['regiao'] ?? null) === '';
    });

    expect(collect($eventos)->contains(
        fn (MessageLogged $event): bool => $event->message === 'prepostagem.item_ok'
            && ($event->context['lote_id'] ?? null) === $lote->id
    ))->toBeTrue();
});

test('status atual 7 consulta ate virar 2', function () {
    Sleep::fake();

    $eventos = [];
    Event::listen(MessageLogged::class, function (MessageLogged $event) use (&$eventos): void {
        $eventos[] = $event;
    });

    $lote = loteComItensClassificados(1);
    $item = $lote->itens->first();

    Http::fake([
        '*/token/v1/autentica/cartaopostagem' => respostaTokenCorreios(),
        '*/prepostagem/v1/prepostagens' => Http::response([
            'id' => 'PP-PENDENTE',
            'codigoObjeto' => 'DG777777777BR',
            'statusAtual' => 7,
            'descStatusAtual' => 'Pendente',
        ], 200),
        '*/prepostagem/v2/prepostagens*' => Http::sequence()
            ->push([
                'itens' => [[
                    'id' => 'PP-PENDENTE',
                    'codigoObjeto' => 'DG777777777BR',
                    'statusAtual' => 7,
                ]],
            ], 200)
            ->push([
                'itens' => [[
                    'id' => 'PP-PENDENTE',
                    'codigoObjeto' => 'DG777777777BR',
                    'statusAtual' => 2,
                    'descStatusAtual' => 'Pré-postado',
                ]],
            ], 200),
    ]);

    $resultado = app(CriarPrePostagemItemLoteCorreiosAction::class)->execute($item);

    expect($resultado->estaConcluida())->toBeTrue();

    $item->refresh();

    expect($item->status)->toBe(InterlabLotePostagemItemStatus::Prepostado)
        ->and($item->status_atual)->toBe(CorreiosPrePostagemStatus::Prepostado)
        ->and($item->id_prepostagem)->toBe('PP-PENDENTE');

    Sleep::assertSequence([
        Sleep::for(5)->seconds(),
        Sleep::for(6)->seconds(),
    ]);

    expect(collect($eventos)->contains(
        fn (MessageLogged $event): bool => $event->message === 'prepostagem.pendente'
    ))->toBeTrue();

    expect(collect($eventos)->contains(
        fn (MessageLogged $event): bool => $event->message === 'prepostagem.poll'
            && ($event->context['intervalo_segundos'] ?? null) === 5
    ))->toBeTrue();

    Http::assertSent(fn (Request $request): bool => str_contains($request->url(), '/prepostagem/v2/prepostagens')
        && ($request['id'] ?? null) === 'PP-PENDENTE');
});

test('status atual 7 com poll esgotado retenta sem marcar prepostado', function () {
    Sleep::fake();

    $lote = loteComItensClassificados(1);
    $item = $lote->itens->first();

    Http::fake([
        '*/token/v1/autentica/cartaopostagem' => respostaTokenCorreios(),
        '*/prepostagem/v1/prepostagens' => Http::response([
            'id' => 'PP-POLL',
            'codigoObjeto' => 'DG888888888BR',
            'statusAtual' => 7,
        ], 200),
        '*/prepostagem/v2/prepostagens*' => Http::response([
            'itens' => [[
                'id' => 'PP-POLL',
                'codigoObjeto' => 'DG888888888BR',
                'statusAtual' => 7,
            ]],
        ], 200),
    ]);

    $resultado = app(CriarPrePostagemItemLoteCorreiosAction::class)->execute($item, 1);

    expect($resultado->deveRetentar())->toBeTrue()
        ->and($resultado->atrasoSegundos)->toBe(5)
        ->and($item->fresh()->status)->toBe(InterlabLotePostagemItemStatus::Classificado)
        ->and($item->fresh()->id_prepostagem)->toBe('PP-POLL')
        ->and($item->fresh()->status_atual)->toBe(CorreiosPrePostagemStatus::Pendente)
        ->and($lote->fresh()->status)->not->toBe(InterlabLotePostagemStatus::Erro);

    Sleep::assertSequence([
        Sleep::for(5)->seconds(),
        Sleep::for(6)->seconds(),
        Sleep::for(7)->seconds(),
        Sleep::for(8)->seconds(),
        Sleep::for(9)->seconds(),
    ]);

    Mail::assertNothingQueued();
});

test('status terminal na criacao encerra o lote', function () {
    $lote = loteComItensClassificados(1);
    $item = $lote->itens->first();

    Http::fake([
        '*/token/v1/autentica/cartaopostagem' => respostaTokenCorreios(),
        '*/prepostagem/v1/prepostagens' => Http::response([
            'id' => 'PP-999',
            'codigoObjeto' => 'DG999999999BR',
            'statusAtual' => 5,
            'motivoCancelamento' => 'Falha na geração da DC-e',
        ], 200),
    ]);

    $resultado = app(CriarPrePostagemItemLoteCorreiosAction::class)->execute($item);

    expect($resultado->estaFalhou())->toBeTrue();

    $lote->refresh();
    $item->refresh();

    expect($lote->status)->toBe(InterlabLotePostagemStatus::Erro)
        ->and($lote->etapa_erro)->toBe(InterlabLotePostagemStatus::GerandoPrePostagens->value)
        ->and($lote->erro_mensagem)->toContain('Falha na geração da DC-e')
        ->and($item->status)->not->toBe(InterlabLotePostagemItemStatus::Prepostado)
        ->and($item->id_prepostagem)->toBe('PP-999');

    Mail::assertQueued(InterlabLotePostagemErroMail::class);
});

test('status terminal no poll inclui motivoCancelamento', function () {
    Sleep::fake();

    $lote = loteComItensClassificados(1);
    $item = $lote->itens->first();

    Http::fake([
        '*/token/v1/autentica/cartaopostagem' => respostaTokenCorreios(),
        '*/prepostagem/v1/prepostagens' => Http::response([
            'id' => 'PP-CANCEL',
            'codigoObjeto' => 'DG555555555BR',
            'statusAtual' => 7,
        ], 200),
        '*/prepostagem/v2/prepostagens*' => Http::response([
            'itens' => [[
                'id' => 'PP-CANCEL',
                'codigoObjeto' => 'DG555555555BR',
                'statusAtual' => 5,
                'motivoCancelamento' => 'DC-e rejeitada pela SEFAZ',
            ]],
        ], 200),
    ]);

    $resultado = app(CriarPrePostagemItemLoteCorreiosAction::class)->execute($item);

    expect($resultado->estaFalhou())->toBeTrue();

    $lote->refresh();

    expect($lote->status)->toBe(InterlabLotePostagemStatus::Erro)
        ->and($lote->etapa_erro)->toBe(InterlabLotePostagemStatus::GerandoPrePostagens->value)
        ->and($lote->erro_mensagem)->toContain('DC-e rejeitada pela SEFAZ');

    Mail::assertQueued(InterlabLotePostagemErroMail::class);
});

test('http 400 encerra sem renovar token', function () {
    $lote = loteComItensClassificados(1);

    Http::fake([
        '*/token/v1/autentica/cartaopostagem' => respostaTokenCorreios(),
        '*/prepostagem/v1/prepostagens' => Http::response(['msgs' => ['campo inválido']], 400),
    ]);

    $resultado = app(CriarPrePostagemItemLoteCorreiosAction::class)->execute($lote->itens->first());

    expect($resultado->estaFalhou())->toBeTrue()
        ->and($lote->fresh()->status)->toBe(InterlabLotePostagemStatus::Erro);

    expect(Http::recorded()->filter(fn (array $par): bool => str_contains($par[0]->url(), '/token/'))->count())->toBe(1);
});

test('http 429 retenta sem marcar prepostado', function () {
    $lote = loteComItensClassificados(1);
    $item = $lote->itens->first();

    Http::fake([
        '*/token/v1/autentica/cartaopostagem' => respostaTokenCorreios(),
        '*/prepostagem/v1/prepostagens' => Http::response([], 429, ['Retry-After' => '8']),
    ]);

    $resultado = app(CriarPrePostagemItemLoteCorreiosAction::class)->execute($item);

    expect($resultado->deveRetentar())->toBeTrue()
        ->and($resultado->atrasoSegundos)->toBe(8)
        ->and($item->fresh()->status)->toBe(InterlabLotePostagemItemStatus::Classificado)
        ->and($item->fresh()->id_prepostagem)->toBeNull();
});

test('item com id prepostagem e status 2 nao reposta', function () {
    $lote = loteComItensClassificados(1);
    $item = $lote->itens->first();
    $item->update([
        'id_prepostagem' => 'PP-EXISTENTE',
        'codigo_objeto' => 'DG000000001BR',
        'status_atual' => CorreiosPrePostagemStatus::Prepostado,
        'status' => InterlabLotePostagemItemStatus::Classificado,
    ]);

    Http::fake([
        '*/token/v1/autentica/cartaopostagem' => respostaTokenCorreios(),
    ]);

    $resultado = app(CriarPrePostagemItemLoteCorreiosAction::class)->execute($item->fresh());

    expect($resultado->estaConcluida())->toBeTrue()
        ->and($item->fresh()->status)->toBe(InterlabLotePostagemItemStatus::Prepostado);

    Http::assertNothingSent();
});

test('item com id prepostagem e status 7 retoma so a consulta', function () {
    Sleep::fake();

    $lote = loteComItensClassificados(1);
    $item = $lote->itens->first();
    $item->update([
        'id_prepostagem' => 'PP-RETORNO',
        'codigo_objeto' => 'DG555555555BR',
        'status_atual' => CorreiosPrePostagemStatus::Pendente,
        'status' => InterlabLotePostagemItemStatus::Classificado,
    ]);

    Http::fake([
        '*/token/v1/autentica/cartaopostagem' => respostaTokenCorreios(),
        '*/prepostagem/v2/prepostagens*' => Http::response([
            'itens' => [[
                'id' => 'PP-RETORNO',
                'codigoObjeto' => 'DG555555555BR',
                'statusAtual' => 2,
            ]],
        ], 200),
    ]);

    $resultado = app(CriarPrePostagemItemLoteCorreiosAction::class)->execute($item->fresh());

    expect($resultado->estaConcluida())->toBeTrue()
        ->and($item->fresh()->status)->toBe(InterlabLotePostagemItemStatus::Prepostado);

    Http::assertNotSent(fn (Request $request): bool => str_contains($request->url(), '/prepostagem/v1/prepostagens')
        && ! str_contains($request->url(), 'rotulo')
        && $request->method() === 'POST');

    Http::assertSent(fn (Request $request): bool => str_contains($request->url(), '/prepostagem/v2/prepostagens'));

    Sleep::assertSequence([
        Sleep::for(5)->seconds(),
    ]);
});
