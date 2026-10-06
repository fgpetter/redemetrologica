<?php

use App\Actions\Interlab\EnfileirarRastreioItensLotePostagemAction;
use App\Actions\Interlab\RastrearItemLotePostagemAction;
use App\Enums\InterlabLotePostagemItemStatus;
use App\Enums\InterlabLotePostagemStatus;
use App\Jobs\Interlab\RastrearItemLotePostagemJob;
use App\Mail\InterlabLotePostagemRastreioMail;
use App\Models\InterlabLotePostagem;
use Database\Factories\InterlabInscritoFactory;
use Database\Factories\InterlabLotePostagemFactory;
use Database\Factories\InterlabLotePostagemItemFactory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    configurarPrePostagemCorreios();
    Mail::fake();
});

test('persiste payload status e ultimo evento do rastro', function () {
    $item = itemRastreavel();

    Http::fake(function (Request $request) use ($item) {
        if (str_contains($request->url(), '/token/')) {
            return respostaTokenCorreios();
        }

        expect($request->url())->toContain('/srorastro/v1/objetos/'.$item->codigo_objeto)
            ->and($request->data()['resultado'] ?? null)->toBe('T');

        return Http::response(respostaRastroCorreios(
            $item->codigo_objeto,
            eventosRastroEmTransito(),
        ), 200);
    });

    $resultado = app(RastrearItemLotePostagemAction::class)->execute($item);

    expect($resultado->status_correios)->toBe('DO-01')
        ->and($resultado->ultimo_evento)->toBe('Objeto em trânsito')
        ->and($resultado->rastreado_em)->not->toBeNull()
        ->and($resultado->rastreio_payload['codObjeto'] ?? null)->toBe($item->codigo_objeto)
        ->and($resultado->status)->toBe(InterlabLotePostagemItemStatus::EtiquetaGerada);
});

test('evento de entrega marca item como entregue', function () {
    $item = itemRastreavel();

    Http::fake(function (Request $request) use ($item) {
        if (str_contains($request->url(), '/token/')) {
            return respostaTokenCorreios();
        }

        return Http::response(respostaRastroCorreios(
            $item->codigo_objeto,
            eventosRastroEntrega(),
        ), 200);
    });

    $resultado = app(RastrearItemLotePostagemAction::class)->execute($item);

    expect($resultado->status)->toBe(InterlabLotePostagemItemStatus::Entregue)
        ->and($resultado->status_correios)->toBe('BDE-01')
        ->and($resultado->ultimo_evento)->toBe('Objeto entregue ao destinatário');
});

test('job notifica na primeira mudanca de status e nao reenvia se inalterado', function () {
    $item = itemRastreavel([
        'email' => 'lab@example.com',
        'responsavel_tecnico' => 'Ana Técnica',
    ]);

    Http::fake(function (Request $request) use ($item) {
        if (str_contains($request->url(), '/token/')) {
            return respostaTokenCorreios();
        }

        return Http::response(respostaRastroCorreios(
            $item->codigo_objeto,
            eventosRastroEmTransito(),
        ), 200);
    });

    (new RastrearItemLotePostagemJob($item))->handle(app(RastrearItemLotePostagemAction::class));

    Mail::assertQueued(InterlabLotePostagemRastreioMail::class, function (InterlabLotePostagemRastreioMail $mail) use ($item): bool {
        $mail->assertSeeInHtml('Objeto em trânsito');
        $mail->assertSeeInHtml((string) $item->codigo_objeto);
        $mail->assertSeeInHtml('Laboratório Teste');
        $mail->assertSeeInHtml('https://rastreamento.correios.com.br/app/index.php');

        return $mail->item->is($item)
            && $mail->hasTo('lab@example.com')
            && $mail->hasCc('tecnico@redemetrologica.com.br')
            && $mail->hasCc('interlab@redemetrologica.com.br')
            && $mail->tries === 3;
    });

    Mail::fake();

    (new RastrearItemLotePostagemJob($item->fresh()))->handle(app(RastrearItemLotePostagemAction::class));

    Mail::assertNothingQueued();
});

test('sem eventos nao altera status_correios nem envia email', function () {
    $item = itemRastreavel(['email' => 'lab@example.com']);

    Http::fake(function (Request $request) use ($item) {
        if (str_contains($request->url(), '/token/')) {
            return respostaTokenCorreios();
        }

        return Http::response(respostaRastroCorreios(
            $item->codigo_objeto,
            [],
            'Objeto ainda não encontrado no fluxo postal',
        ), 200);
    });

    (new RastrearItemLotePostagemJob($item))->handle(app(RastrearItemLotePostagemAction::class));

    $item->refresh();

    expect($item->status_correios)->toBeNull()
        ->and($item->rastreado_em)->not->toBeNull()
        ->and($item->rastreio_payload['mensagem'] ?? null)->toBe('Objeto ainda não encontrado no fluxo postal');

    Mail::assertNothingQueued();
});

test('http 401 nao encerra lote nem marca entregue', function () {
    $lote = InterlabLotePostagemFactory::new()->create([
        'status' => InterlabLotePostagemStatus::Concluido,
    ]);
    $item = itemRastreavel(lote: $lote);

    Http::fake(function (Request $request) {
        if (str_contains($request->url(), '/token/')) {
            return respostaTokenCorreios();
        }

        return Http::response(['msgs' => ['Não autorizado']], 401);
    });

    (new RastrearItemLotePostagemJob($item))->handle(app(RastrearItemLotePostagemAction::class));

    $item->refresh();
    $lote->refresh();

    expect($item->status)->toBe(InterlabLotePostagemItemStatus::EtiquetaGerada)
        ->and($item->status_correios)->toBeNull()
        ->and($lote->status)->toBe(InterlabLotePostagemStatus::Concluido);

    Mail::assertNothingQueued();
});

test('comando enfileira jobs com delay de 5s e ignora entregues', function () {
    Queue::fake([RastrearItemLotePostagemJob::class]);

    $candidatoA = itemRastreavel();
    $candidatoB = itemRastreavel();
    InterlabLotePostagemItemFactory::new()->entregue()->create();

    $this->artisan('interlab:rastrear-itens-postagem')
        ->expectsOutputToContain('Enfileirados: 2')
        ->assertSuccessful();

    $delays = [];

    Queue::assertPushed(RastrearItemLotePostagemJob::class, function (RastrearItemLotePostagemJob $job) use ($candidatoA, $candidatoB, &$delays): bool {
        if (! in_array($job->item->id, [$candidatoA->id, $candidatoB->id], true)) {
            return false;
        }

        $delays[$job->item->id] = $job->delay;

        return true;
    });

    Queue::assertPushed(RastrearItemLotePostagemJob::class, 2);

    expect(collect($delays)->map(fn ($delay) => (int) round(now()->diffInSeconds($delay, false)))->sort()->values()->all())
        ->toEqual([0, 5]);
});

test('comando nao enfileira item ja entregue apos rastreio', function () {
    Queue::fake([RastrearItemLotePostagemJob::class]);

    $item = itemRastreavel();

    Http::fake(function (Request $request) use ($item) {
        if (str_contains($request->url(), '/token/')) {
            return respostaTokenCorreios();
        }

        return Http::response(respostaRastroCorreios(
            $item->codigo_objeto,
            eventosRastroEntrega(),
        ), 200);
    });

    app(RastrearItemLotePostagemAction::class)->execute($item);

    expect($item->fresh()->status)->toBe(InterlabLotePostagemItemStatus::Entregue);

    $this->artisan('interlab:rastrear-itens-postagem')
        ->expectsOutputToContain('Enfileirados: 0')
        ->assertSuccessful();

    Queue::assertNothingPushed();
});

test('comando pula item com job ja na fila', function () {
    config(['queue.default' => 'database']);
    Queue::fake([RastrearItemLotePostagemJob::class]);

    $item = itemRastreavel();

    $payload = json_encode([
        'displayName' => RastrearItemLotePostagemJob::class,
        'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
        'data' => [
            'commandName' => RastrearItemLotePostagemJob::class,
            'command' => 'O:45:"App\\Jobs\\Interlab\\RastrearItemLotePostagemJob":1:{s:4:"item";O:49:"Illuminate\\Contracts\\Database\\ModelIdentifier":4:{s:5:"class";s:36:"App\\Models\\InterlabLotePostagemItem";s:2:"id";i:'.$item->id.';s:9:"relations";a:0:{}s:10:"connection";s:5:"sqlite";}}',
        ],
    ], JSON_THROW_ON_ERROR);

    DB::table('jobs')->insert([
        'queue' => 'default',
        'payload' => $payload,
        'attempts' => 0,
        'reserved_at' => null,
        'available_at' => now()->timestamp,
        'created_at' => now()->timestamp,
    ]);

    $resultado = app(EnfileirarRastreioItensLotePostagemAction::class)->execute();

    expect($resultado['enfileirados'])->toBe(0)
        ->and($resultado['pulados'])->toBe(1);

    Queue::assertNothingPushed();
});

/**
 * @param  array{email?: string|null, responsavel_tecnico?: string|null}  $inscrito
 */
function itemRastreavel(array $inscrito = [], ?InterlabLotePostagem $lote = null): \App\Models\InterlabLotePostagemItem
{
    $lote ??= InterlabLotePostagemFactory::new()->create([
        'status' => InterlabLotePostagemStatus::Concluido,
    ]);

    $inscritoModel = InterlabInscritoFactory::new()->create([
        'email' => $inscrito['email'] ?? 'lab@example.com',
        'responsavel_tecnico' => $inscrito['responsavel_tecnico'] ?? 'Responsável Técnico',
    ]);

    return InterlabLotePostagemItemFactory::new()
        ->etiquetaGerada()
        ->create([
            'interlab_lote_postagem_id' => $lote->id,
            'interlab_inscrito_id' => $inscritoModel->id,
        ]);
}
