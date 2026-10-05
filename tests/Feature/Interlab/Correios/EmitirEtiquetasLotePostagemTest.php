<?php

use App\Actions\Interlab\EmitirEtiquetasLotePostagemAction;
use App\Enums\InterlabLotePostagemEtiquetaStatus;
use App\Enums\InterlabLotePostagemItemStatus;
use App\Enums\InterlabLotePostagemStatus;
use App\Mail\InterlabLotePostagemErroMail;
use App\Models\InterlabLotePostagem;
use Database\Factories\InterlabLotePostagemEtiquetaFactory;
use Database\Factories\InterlabLotePostagemFactory;
use Database\Factories\InterlabLotePostagemItemFactory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Sleep;

beforeEach(function () {
    configurarPrePostagemCorreios();
    Mail::fake();
    Storage::fake('local');
    Sleep::fake();
});

test('emite etiquetas em grupos de ate 4 com layout padrao', function () {
    $lote = loteComItensPrepostados(5);

    Http::fake(function (Request $request) {
        if (str_contains($request->url(), '/token/')) {
            return respostaTokenCorreios();
        }

        if (str_contains($request->url(), '/rotulo/assincrono/pdf')) {
            expect(count($request->data()['idsPrePostagem'] ?? []))->toBeLessThanOrEqual(4)
                ->and($request->data()['tipoRotulo'] ?? null)->toBe('P')
                ->and($request->data()['formatoRotulo'] ?? null)->toBe('ET')
                ->and($request->data()['imprimeRemetente'] ?? null)->toBe('S')
                ->and($request->data()['layoutImpressao'] ?? null)->toBe('PADRAO');

            return Http::response(['idRecibo' => 'recibo-'.md5(json_encode($request->data()['idsPrePostagem']))], 200);
        }

        if (str_contains($request->url(), '/rotulo/download/assincrono/')) {
            return Http::response([
                'dados' => base64_encode('%PDF-1.4 etiqueta'),
            ], 200);
        }

        return Http::response(['msgs' => ['não esperado']], 500);
    });

    $resultado = app(EmitirEtiquetasLotePostagemAction::class)->execute($lote);

    expect($resultado->estaConcluida())->toBeTrue();

    $lote->refresh()->load(['etiquetas', 'itens']);

    expect($lote->etiquetas)->toHaveCount(2)
        ->and($lote->etiquetas->every(fn ($e) => $e->status === InterlabLotePostagemEtiquetaStatus::Gerada))->toBeTrue()
        ->and($lote->itens->every(fn ($i) => $i->status === InterlabLotePostagemItemStatus::EtiquetaGerada))->toBeTrue();

    foreach ($lote->etiquetas as $etiqueta) {
        Storage::disk('local')->assertExists($etiqueta->etiqueta_path);
    }

    expect(
        Http::recorded()->filter(fn (array $par): bool => str_contains($par[0]->url(), '/rotulo/assincrono/pdf'))->count()
    )->toBe(2);
});

test('com etiqueta path nao chama a api', function () {
    $lote = loteComItensPrepostados(1);
    $item = $lote->itens->first();
    $etiqueta = InterlabLotePostagemEtiquetaFactory::new()->gerada()->create([
        'interlab_lote_postagem_id' => $lote->id,
        'etiqueta_path' => 'correios/etiquetas/ja-existe.pdf',
    ]);
    Storage::disk('local')->put('correios/etiquetas/ja-existe.pdf', '%PDF-1.4');
    $item->update([
        'interlab_lote_postagem_etiqueta_id' => $etiqueta->id,
        'status' => InterlabLotePostagemItemStatus::EtiquetaGerada,
    ]);

    Http::fake([
        '*/token/v1/autentica/cartaopostagem' => respostaTokenCorreios(),
    ]);

    $resultado = app(EmitirEtiquetasLotePostagemAction::class)->execute($lote->fresh());

    expect($resultado->estaConcluida())->toBeTrue();
    Http::assertNothingSent();
});

test('com recibo apenas retoma o download', function () {
    $lote = loteComItensPrepostados(1);
    $item = $lote->itens->first();
    $etiqueta = InterlabLotePostagemEtiquetaFactory::new()->create([
        'interlab_lote_postagem_id' => $lote->id,
        'status' => InterlabLotePostagemEtiquetaStatus::Solicitada,
        'solicitacao_iniciada_em' => now(),
        'id_recibo_rotulo' => 'recibo-existente',
    ]);
    $item->update(['interlab_lote_postagem_etiqueta_id' => $etiqueta->id]);

    Http::fake([
        '*/token/v1/autentica/cartaopostagem' => respostaTokenCorreios(),
        '*/rotulo/download/assincrono/*' => Http::response([
            'dados' => base64_encode('%PDF-1.4 retomado'),
        ], 200),
    ]);

    $resultado = app(EmitirEtiquetasLotePostagemAction::class)->execute($lote->fresh());

    expect($resultado->estaConcluida())->toBeTrue()
        ->and($etiqueta->fresh()->status)->toBe(InterlabLotePostagemEtiquetaStatus::Gerada);

    expect(
        Http::recorded()->filter(fn (array $par): bool => str_contains($par[0]->url(), '/rotulo/assincrono/pdf'))->count()
    )->toBe(0);
    expect(
        Http::recorded()->filter(fn (array $par): bool => str_contains($par[0]->url(), '/download/assincrono/'))->count()
    )->toBe(1);
});

test('solicitada sem recibo encerra sem novo post', function () {
    $lote = loteComItensPrepostados(1);
    $item = $lote->itens->first();
    $etiqueta = InterlabLotePostagemEtiquetaFactory::new()->solicitada()->create([
        'interlab_lote_postagem_id' => $lote->id,
    ]);
    $item->update(['interlab_lote_postagem_etiqueta_id' => $etiqueta->id]);

    Http::fake([
        '*/token/v1/autentica/cartaopostagem' => respostaTokenCorreios(),
    ]);

    $resultado = app(EmitirEtiquetasLotePostagemAction::class)->execute($lote->fresh());

    expect($resultado->estaFalhou())->toBeTrue()
        ->and($lote->fresh()->status)->toBe(InterlabLotePostagemStatus::Erro)
        ->and($etiqueta->fresh()->status)->toBe(InterlabLotePostagemEtiquetaStatus::Erro);

    Http::assertNothingSent();
    Mail::assertQueued(InterlabLotePostagemErroMail::class);
});

test('poll esgotado mantém recibo e retenta', function () {
    $lote = loteComItensPrepostados(1);

    Http::fake([
        '*/token/v1/autentica/cartaopostagem' => respostaTokenCorreios(),
        '*/rotulo/assincrono/pdf' => Http::response(['idRecibo' => 'recibo-poll'], 200),
        '*/rotulo/download/assincrono/*' => Http::response(['status' => 'processando'], 200),
    ]);

    $resultado = app(EmitirEtiquetasLotePostagemAction::class)->execute($lote);

    expect($resultado->deveRetentar())->toBeTrue();

    $etiqueta = $lote->fresh()->etiquetas()->first();

    expect($etiqueta->id_recibo_rotulo)->toBe('recibo-poll')
        ->and($etiqueta->status)->toBe(InterlabLotePostagemEtiquetaStatus::Solicitada)
        ->and($etiqueta->etiqueta_path)->toBeNull();

    Sleep::assertSleptTimes(4);
});

function loteComItensPrepostados(int $quantidade = 1): InterlabLotePostagem
{
    $lote = InterlabLotePostagemFactory::new()->create([
        'status' => InterlabLotePostagemStatus::GerandoEtiquetas,
        'ultima_progressao_em' => now(),
    ]);

    InterlabLotePostagemItemFactory::new()
        ->count($quantidade)
        ->prepostado()
        ->create(['interlab_lote_postagem_id' => $lote->id]);

    return $lote->load('itens');
}
