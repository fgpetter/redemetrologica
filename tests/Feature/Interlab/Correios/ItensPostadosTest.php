<?php

use App\Enums\InterlabLotePostagemStatus;
use App\Livewire\Interlab\ItensPostados;
use Database\Factories\AgendaInterlabFactory;
use Database\Factories\InterlabLotePostagemFactory;
use Database\Factories\InterlabLotePostagemItemFactory;
use Livewire\Livewire;

test('mostra um card por lote concluido com itens, zip e status de cada objeto', function () {
    $agenda = AgendaInterlabFactory::new()->create();
    $lote = InterlabLotePostagemFactory::new()->create([
        'agenda_interlab_id' => $agenda->id,
        'status' => InterlabLotePostagemStatus::Concluido,
        'nome' => 'Lote setembro concluido',
    ]);

    InterlabLotePostagemItemFactory::new()->entregue()->create([
        'interlab_lote_postagem_id' => $lote->id,
        'destinatario' => ['nome' => 'Lab Alfa'],
    ]);
    InterlabLotePostagemItemFactory::new()->etiquetaGerada()->create([
        'interlab_lote_postagem_id' => $lote->id,
        'destinatario' => ['nome' => 'Lab Beta'],
    ]);

    Livewire::test(ItensPostados::class, ['agenda' => $agenda->fresh()])
        ->assertSee('Lote setembro concluido')
        ->assertSee('2 itens')
        ->assertSeeHtml(route('agenda-interlab-lote-postagem-documentos', $lote))
        ->assertSee('Baixar documentos (.zip)')
        ->assertSee('Lab Alfa')
        ->assertSee('Entregue')
        ->assertSee('Lab Beta')
        ->assertSee('Etiqueta gerada');
});

test('nao lista lotes em processamento nem com erro', function () {
    $agenda = AgendaInterlabFactory::new()->create();

    InterlabLotePostagemFactory::new()->create([
        'agenda_interlab_id' => $agenda->id,
        'status' => InterlabLotePostagemStatus::GerandoEtiquetas,
        'nome' => 'Lote em andamento',
    ]);
    InterlabLotePostagemFactory::new()->create([
        'agenda_interlab_id' => $agenda->id,
        'status' => InterlabLotePostagemStatus::Erro,
        'nome' => 'Lote com erro',
    ]);

    Livewire::test(ItensPostados::class, ['agenda' => $agenda->fresh()])
        ->assertDontSee('Lote em andamento')
        ->assertDontSee('Lote com erro')
        ->assertDontSee('Baixar documentos (.zip)');
});

test('mostra estado vazio quando nao ha lote concluido', function () {
    $agenda = AgendaInterlabFactory::new()->create();

    Livewire::test(ItensPostados::class, ['agenda' => $agenda])
        ->assertSee('Nenhum lote de postagem concluído nesta agenda.');
});
