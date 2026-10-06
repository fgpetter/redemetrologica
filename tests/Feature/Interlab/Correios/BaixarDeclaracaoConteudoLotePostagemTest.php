<?php

use App\Actions\Interlab\BaixarDaceLotePostagemAction;
use App\Enums\InterlabLotePostagemItemStatus;
use App\Enums\InterlabLotePostagemStatus;
use App\Mail\InterlabLotePostagemDocumentosMail;
use App\Models\InterlabLotePostagem;
use Database\Factories\InterlabLotePostagemEtiquetaFactory;
use Database\Factories\InterlabLotePostagemFactory;
use Database\Factories\InterlabLotePostagemItemFactory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    configurarPrePostagemCorreios();
    Mail::fake();
    Storage::fake('local');
});

test('baixa dace completa em um unico pdf', function () {
    $lote = loteProntoParaDeclaracao(3);
    $ids = $lote->itens()->orderBy('id')->pluck('id_prepostagem')->values()->all();

    Http::fake(function (Request $request) use ($ids) {
        if (str_contains($request->url(), '/token/')) {
            return respostaTokenCorreios();
        }

        if (str_contains($request->url(), '/dce/dace/impressao')) {
            expect($request->data()['idsPrePostagens'] ?? null)->toBe($ids)
                ->and($request->data()['tipoDace'] ?? null)->toBe('C');

            return Http::response([
                'objetos' => $ids,
                'dados' => base64_encode("%PDF-1.4\n"),
            ], 200);
        }

        return Http::response(['msgs' => ['não esperado']], 500);
    });

    $resultado = app(BaixarDaceLotePostagemAction::class)->execute($lote);

    expect($resultado->estaConcluida())->toBeTrue();

    $lote->refresh();

    expect($lote->status)->toBe(InterlabLotePostagemStatus::Concluido)
        ->and($lote->dace_pdf_path)->toBe('correios/dace/'.$lote->uid.'.pdf')
        ->and($lote->documentos_enviados_em)->not->toBeNull();

    Storage::disk('local')->assertExists($lote->dace_pdf_path);
    expect(Storage::disk('local')->get($lote->dace_pdf_path))->toStartWith('%PDF');

    Mail::assertQueued(InterlabLotePostagemDocumentosMail::class, function (InterlabLotePostagemDocumentosMail $mail) use ($lote): bool {
        return $mail->lote->is($lote)
            && $mail->hasTo('tecnico@redemetrologica.com.br')
            && $mail->hasTo('interlab@redemetrologica.com.br')
            && collect($mail->attachments())->contains(
                fn ($anexo): bool => ($anexo->as ?? null) === 'dace.pdf'
            );
    });
});

test('pdf da dace existente nao faz chamadas', function () {
    $lote = loteProntoParaDeclaracao(1);
    $pdf = 'correios/dace/'.$lote->uid.'.pdf';
    Storage::disk('local')->put($pdf, '%PDF-1.4');
    $lote->update(['dace_pdf_path' => $pdf]);

    Http::fake([
        '*/token/v1/autentica/cartaopostagem' => respostaTokenCorreios(),
    ]);

    $resultado = app(BaixarDaceLotePostagemAction::class)->execute($lote->fresh());

    expect($resultado->estaConcluida())->toBeTrue()
        ->and($lote->fresh()->status)->toBe(InterlabLotePostagemStatus::Concluido)
        ->and($lote->fresh()->dace_pdf_path)->toBe($pdf)
        ->and($lote->fresh()->documentos_enviados_em)->not->toBeNull();

    Http::assertNothingSent();
    Mail::assertQueued(InterlabLotePostagemDocumentosMail::class);
});

test('nao reenvia email de documentos na segunda execucao', function () {
    $lote = loteProntoParaDeclaracao(1);
    $pdf = 'correios/dace/'.$lote->uid.'.pdf';
    Storage::disk('local')->put($pdf, '%PDF-1.4');
    $lote->update([
        'status' => InterlabLotePostagemStatus::Concluido,
        'dace_pdf_path' => $pdf,
        'documentos_enviados_em' => now(),
    ]);

    Http::fake([
        '*/token/v1/autentica/cartaopostagem' => respostaTokenCorreios(),
    ]);

    $resultado = app(BaixarDaceLotePostagemAction::class)->execute($lote->fresh());

    expect($resultado->estaConcluida())->toBeTrue();
    Mail::assertNothingQueued();
});

test('http 400 encerra o lote na dace', function () {
    $lote = loteProntoParaDeclaracao(1);

    Http::fake([
        '*/token/v1/autentica/cartaopostagem' => respostaTokenCorreios(),
        '*/prepostagem/v1/prepostagens/dce/dace/impressao' => Http::response(['msgs' => ['dace inválida']], 400),
    ]);

    $resultado = app(BaixarDaceLotePostagemAction::class)->execute($lote);

    expect($resultado->estaFalhou())->toBeTrue()
        ->and($lote->fresh()->status)->toBe(InterlabLotePostagemStatus::Erro);
});

test('http 429 retenta sem encerrar o lote na dace', function () {
    $lote = loteProntoParaDeclaracao(1);

    Http::fake([
        '*/token/v1/autentica/cartaopostagem' => respostaTokenCorreios(),
        '*/prepostagem/v1/prepostagens/dce/dace/impressao' => Http::response(['msgs' => ['aguarde']], 429, [
            'Retry-After' => '3',
        ]),
    ]);

    $resultado = app(BaixarDaceLotePostagemAction::class)->execute($lote);

    expect($resultado->deveRetentar())->toBeTrue()
        ->and($resultado->atrasoSegundos)->toBeGreaterThanOrEqual(3)
        ->and($lote->fresh()->status)->not->toBe(InterlabLotePostagemStatus::Erro);
});

test('http 200 sem pdf utilizavel encerra o lote', function () {
    $lote = loteProntoParaDeclaracao(1);

    Http::fake([
        '*/token/v1/autentica/cartaopostagem' => respostaTokenCorreios(),
        '*/prepostagem/v1/prepostagens/dce/dace/impressao' => Http::response([
            'objetos' => ['PP-1'],
            'dados' => base64_encode('nao-e-pdf'),
        ], 200),
    ]);

    $resultado = app(BaixarDaceLotePostagemAction::class)->execute($lote);

    expect($resultado->estaFalhou())->toBeTrue()
        ->and($lote->fresh()->status)->toBe(InterlabLotePostagemStatus::Erro)
        ->and($lote->fresh()->erro_mensagem)->toContain('PDF utilizável');
});

function loteProntoParaDeclaracao(int $quantidade = 1): InterlabLotePostagem
{
    $lote = InterlabLotePostagemFactory::new()->create([
        'status' => InterlabLotePostagemStatus::GerandoDeclaracao,
        'ultima_progressao_em' => now(),
    ]);

    $itens = InterlabLotePostagemItemFactory::new()
        ->count($quantidade)
        ->prepostado()
        ->create([
            'interlab_lote_postagem_id' => $lote->id,
            'status' => InterlabLotePostagemItemStatus::EtiquetaGerada,
        ]);

    $etiqueta = InterlabLotePostagemEtiquetaFactory::new()->gerada()->create([
        'interlab_lote_postagem_id' => $lote->id,
    ]);

    $itens->each->update(['interlab_lote_postagem_etiqueta_id' => $etiqueta->id]);

    return $lote->load(['itens', 'etiquetas']);
}
