<?php

use App\Actions\Interlab\ClassificarServicoItensLotePostagemCorreiosAction;
use App\Actions\Interlab\ProcessarInterlabLotePostagemAction;
use App\Enums\InterlabLotePostagemItemStatus;
use App\Enums\InterlabLotePostagemStatus;
use App\Jobs\Interlab\ClassificarServicoLotePostagemJob;
use App\Jobs\Interlab\ProcessarInterlabLotePostagemJob;
use App\Livewire\Interlab\GerarLotePostagem;
use App\Mail\InterlabLotePostagemErroMail;
use Database\Factories\InterlabLotePostagemFactory;
use Database\Factories\InterlabLotePostagemItemFactory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Sleep;
use Livewire\Livewire;

beforeEach(function () {
    configurarPrePostagemCorreios();
    Mail::fake();
    Storage::fake('local');
    Sleep::fake();
});

/**
 * @return callable(Request): \GuzzleHttp\Promise\PromiseInterface
 */
function fakeHttpOrquestradorCompleto(): callable
{
    $contador = 0;

    return function (Request $request) use (&$contador) {
        if (str_contains($request->url(), '/token/')) {
            return respostaTokenCorreios();
        }

        if (str_ends_with(parse_url($request->url(), PHP_URL_PATH) ?? '', '/prepostagens')) {
            $contador++;

            return Http::response([
                'id' => 'PP-'.$contador,
                'codigoObjeto' => sprintf('DG%09dBR', $contador),
                'statusAtual' => 2,
            ], 200);
        }

        if (str_contains($request->url(), '/rotulo/assincrono/pdf')) {
            return Http::response(['idRecibo' => 'recibo-orq'], 200);
        }

        if (str_contains($request->url(), '/rotulo/download/assincrono/')) {
            return Http::response(['dados' => base64_encode('%PDF-1.4')], 200);
        }

        if (str_contains($request->url(), '/dce/dace/impressao')) {
            return Http::response([
                'objetos' => ['PP-1'],
                'dados' => base64_encode("%PDF-1.4\n"),
            ], 200);
        }

        return Http::response(['msgs' => ['path inesperado: '.$request->url()]], 500);
    };
}

test('orquestrador faz handoff entre etapas ate concluido', function () {
    $lote = InterlabLotePostagemFactory::new()->create([
        'status' => InterlabLotePostagemStatus::ClassificandoServico,
        'ultima_progressao_em' => now(),
    ]);

    InterlabLotePostagemItemFactory::new()
        ->count(2)
        ->classificadoSedex12()
        ->create(['interlab_lote_postagem_id' => $lote->id]);

    Http::fake(fakeHttpOrquestradorCompleto());

    $resultadoPre = app(ProcessarInterlabLotePostagemAction::class)->execute($lote->fresh());

    expect($resultadoPre->deveRetentar())->toBeTrue()
        ->and($resultadoPre->atrasoSegundos)->toBe(5)
        ->and($lote->fresh()->status)->toBe(InterlabLotePostagemStatus::GerandoEtiquetas)
        ->and($lote->etiquetas()->count())->toBe(0);

    $resultadoEtq = app(ProcessarInterlabLotePostagemAction::class)->execute($lote->fresh());

    expect($resultadoEtq->deveRetentar())->toBeTrue()
        ->and($resultadoEtq->atrasoSegundos)->toBe(5)
        ->and($lote->fresh()->status)->toBe(InterlabLotePostagemStatus::GerandoDeclaracao)
        ->and($lote->itens()->where('status', InterlabLotePostagemItemStatus::EtiquetaGerada)->count())->toBe(2);

    $resultadoDecl = app(ProcessarInterlabLotePostagemAction::class)->execute($lote->fresh());

    expect($resultadoDecl->estaConcluida())->toBeTrue()
        ->and($lote->fresh()->status)->toBe(InterlabLotePostagemStatus::Concluido)
        ->and($lote->fresh()->dace_pdf_path)->not->toBeNull();
});

test('nao inicia etiquetas com item ainda classificado', function () {
    $lote = InterlabLotePostagemFactory::new()->create([
        'status' => InterlabLotePostagemStatus::GerandoPrePostagens,
        'ultima_progressao_em' => now(),
    ]);

    InterlabLotePostagemItemFactory::new()->classificadoSedex12()->create([
        'interlab_lote_postagem_id' => $lote->id,
    ]);
    InterlabLotePostagemItemFactory::new()->prepostado()->create([
        'interlab_lote_postagem_id' => $lote->id,
    ]);

    Http::fake([
        '*/token/v1/autentica/cartaopostagem' => respostaTokenCorreios(),
        '*/prepostagem/v1/prepostagens' => Http::response([], 429),
    ]);

    $resultado = app(ProcessarInterlabLotePostagemAction::class)->execute($lote->fresh());

    expect($resultado->deveRetentar())->toBeTrue()
        ->and($lote->fresh()->status)->toBe(InterlabLotePostagemStatus::GerandoPrePostagens)
        ->and($lote->etiquetas()->count())->toBe(0);
});

test('encerra apos 20 minutos sem progresso', function () {
    $lote = InterlabLotePostagemFactory::new()->create([
        'status' => InterlabLotePostagemStatus::GerandoPrePostagens,
        'ultima_progressao_em' => now(),
    ]);

    InterlabLotePostagemItemFactory::new()->classificadoSedex12()->create([
        'interlab_lote_postagem_id' => $lote->id,
    ]);

    $this->travel(21)->minutes();

    $resultado = app(ProcessarInterlabLotePostagemAction::class)->execute($lote->fresh());

    expect($resultado->estaFalhou())->toBeTrue()
        ->and($lote->fresh()->status)->toBe(InterlabLotePostagemStatus::Erro);

    Mail::assertQueued(InterlabLotePostagemErroMail::class);
});

test('job de classificacao enfileira o processamento apos todos classificados', function () {
    Queue::fake([ProcessarInterlabLotePostagemJob::class]);
    configurarClassificacaoCorreios();

    $lote = InterlabLotePostagemFactory::new()->create([
        'status' => InterlabLotePostagemStatus::ClassificandoServico,
        'ultima_progressao_em' => now(),
    ]);

    $item = InterlabLotePostagemItemFactory::new()->create([
        'interlab_lote_postagem_id' => $lote->id,
    ]);

    Http::fake(fn (Request $request) => respostaPrazoCorreios($request, fn (array $parametro): array => [
        'nuRequisicao' => $parametro['nuRequisicao'],
        'prazoEntrega' => 1,
    ]));

    $job = (new ClassificarServicoLotePostagemJob($lote))->withFakeQueueInteractions();
    $job->handle(app(ClassificarServicoItensLotePostagemCorreiosAction::class));

    expect($item->fresh()->status)->toBe(InterlabLotePostagemItemStatus::Classificado)
        ->and($lote->fresh()->status)->toBe(InterlabLotePostagemStatus::ClassificandoServico);

    Queue::assertPushed(
        ProcessarInterlabLotePostagemJob::class,
        fn (ProcessarInterlabLotePostagemJob $job): bool => $job->lote->is($lote),
    );
});

test('job processar faz release apos handoff de pre postagem', function () {
    config(['queue.default' => 'database']);

    $lote = InterlabLotePostagemFactory::new()->create([
        'status' => InterlabLotePostagemStatus::GerandoPrePostagens,
        'ultima_progressao_em' => now(),
    ]);

    InterlabLotePostagemItemFactory::new()->classificadoSedex12()->create([
        'interlab_lote_postagem_id' => $lote->id,
    ]);

    Http::fake([
        '*/token/v1/autentica/cartaopostagem' => respostaTokenCorreios(),
        '*/prepostagem/v1/prepostagens' => Http::response([
            'id' => 'PP-handoff',
            'codigoObjeto' => 'DG123456789BR',
            'statusAtual' => 2,
        ], 200),
    ]);

    $job = (new ProcessarInterlabLotePostagemJob($lote))->withFakeQueueInteractions();
    $job->handle(app(ProcessarInterlabLotePostagemAction::class));

    $job->assertReleased(delay: 5);
    expect($lote->fresh()->status)->toBe(InterlabLotePostagemStatus::GerandoEtiquetas)
        ->and($lote->etiquetas()->count())->toBe(0);
});

test('livewire mostra status de pre postagem e etiqueta', function () {
    $lote = InterlabLotePostagemFactory::new()->create([
        'status' => InterlabLotePostagemStatus::GerandoPrePostagens,
        'ultima_progressao_em' => now(),
        'nome' => 'Lote UI M3',
    ]);

    InterlabLotePostagemItemFactory::new()->classificadoSedex12()->create([
        'interlab_lote_postagem_id' => $lote->id,
        'destinatario' => [
            'nome' => 'Lab UI M3',
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
        ->assertSee('Lote UI M3')
        ->assertSee('Criando pré-postagem')
        ->assertSeeHtml('wire:poll.5s')
        ->assertDontSee('Laboratórios inscritos')
        ->assertDontSee('Gerar postagem');
});
