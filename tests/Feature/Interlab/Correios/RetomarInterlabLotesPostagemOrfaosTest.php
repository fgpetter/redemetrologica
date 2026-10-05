<?php

use App\Enums\InterlabLotePostagemStatus;
use App\Jobs\Interlab\ProcessarInterlabLotePostagemJob;
use Database\Factories\InterlabLotePostagemFactory;
use Database\Factories\InterlabLotePostagemItemFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    configurarPrePostagemCorreios();
});

test('comando enfileira job para lote gerando etiquetas orfao', function () {
    Queue::fake([ProcessarInterlabLotePostagemJob::class]);

    $lote = InterlabLotePostagemFactory::new()->create([
        'status' => InterlabLotePostagemStatus::GerandoEtiquetas,
        'ultima_progressao_em' => now(),
    ]);

    InterlabLotePostagemItemFactory::new()->prepostado()->create([
        'interlab_lote_postagem_id' => $lote->id,
    ]);

    $this->artisan('interlab:retomar-lotes-postagem-orfaos')
        ->expectsOutputToContain('Enfileirados: 1')
        ->assertSuccessful();

    Queue::assertPushed(
        ProcessarInterlabLotePostagemJob::class,
        fn (ProcessarInterlabLotePostagemJob $job): bool => $job->lote->is($lote),
    );
});

test('comando ignora lotes concluidos e com erro', function () {
    Queue::fake([ProcessarInterlabLotePostagemJob::class]);

    InterlabLotePostagemFactory::new()->create([
        'status' => InterlabLotePostagemStatus::Concluido,
        'ultima_progressao_em' => now(),
    ]);
    InterlabLotePostagemFactory::new()->create([
        'status' => InterlabLotePostagemStatus::Erro,
        'ultima_progressao_em' => now(),
    ]);

    $this->artisan('interlab:retomar-lotes-postagem-orfaos')
        ->expectsOutputToContain('Enfileirados: 0')
        ->assertSuccessful();

    Queue::assertNothingPushed();
});

test('comando nao duplica quando job ja esta na fila', function () {
    config(['queue.default' => 'database']);
    Queue::fake([ProcessarInterlabLotePostagemJob::class]);

    $lote = InterlabLotePostagemFactory::new()->create([
        'status' => InterlabLotePostagemStatus::GerandoEtiquetas,
        'ultima_progressao_em' => now(),
    ]);

    InterlabLotePostagemItemFactory::new()->prepostado()->create([
        'interlab_lote_postagem_id' => $lote->id,
    ]);

    $payload = json_encode([
        'displayName' => ProcessarInterlabLotePostagemJob::class,
        'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
        'data' => [
            'commandName' => ProcessarInterlabLotePostagemJob::class,
            'command' => 'O:48:"App\\Jobs\\Interlab\\ProcessarInterlabLotePostagemJob":1:{s:4:"lote";O:45:"Illuminate\\Contracts\\Database\\ModelIdentifier":4:{s:5:"class";s:32:"App\\Models\\InterlabLotePostagem";s:2:"id";i:'.$lote->id.';s:9:"relations";a:0:{}s:10:"connection";s:5:"sqlite";}}',
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

    $this->artisan('interlab:retomar-lotes-postagem-orfaos')
        ->expectsOutputToContain('Enfileirados: 0; pulados: 1')
        ->assertSuccessful();

    Queue::assertNothingPushed();
});

test('comando enfileira classificando_servico com todos classificados', function () {
    Queue::fake([ProcessarInterlabLotePostagemJob::class]);

    $lote = InterlabLotePostagemFactory::new()->create([
        'status' => InterlabLotePostagemStatus::ClassificandoServico,
        'ultima_progressao_em' => now(),
    ]);

    InterlabLotePostagemItemFactory::new()->classificadoSedex12()->create([
        'interlab_lote_postagem_id' => $lote->id,
    ]);

    $this->artisan('interlab:retomar-lotes-postagem-orfaos')
        ->expectsOutputToContain('Enfileirados: 1')
        ->assertSuccessful();

    Queue::assertPushed(ProcessarInterlabLotePostagemJob::class);
});

test('comando nao enfileira classificando_servico com item pendente', function () {
    Queue::fake([ProcessarInterlabLotePostagemJob::class]);

    $lote = InterlabLotePostagemFactory::new()->create([
        'status' => InterlabLotePostagemStatus::ClassificandoServico,
        'ultima_progressao_em' => now(),
    ]);

    InterlabLotePostagemItemFactory::new()->create([
        'interlab_lote_postagem_id' => $lote->id,
    ]);

    $this->artisan('interlab:retomar-lotes-postagem-orfaos')
        ->expectsOutputToContain('Enfileirados: 0')
        ->assertSuccessful();

    Queue::assertNothingPushed();
});
