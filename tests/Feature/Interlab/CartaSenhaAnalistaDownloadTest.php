<?php

use App\Actions\CriarCartaSenhaAnalistaAction;
use App\Jobs\EnviaSenhaAnalistaJob;
use App\Livewire\Interlab\ListParticipantes;
use App\Models\AgendaInterlab;
use App\Models\DadosGeraDoc;
use App\Models\Endereco;
use App\Models\InterlabAnalista;
use App\Models\InterlabInscrito;
use App\Models\InterlabLaboratorio;
use App\Models\User;
use Database\Factories\AgendaInterlabFactory;
use Database\Factories\InterlabInscritoFactory;
use Database\Factories\PessoaFactory;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

/**
 * @return array{agenda: AgendaInterlab, inscrito: InterlabInscrito, analistas: list<InterlabAnalista>}
 */
function criarInscricaoComAnalistas(
    string $status = 'CONFIRMADO',
    int $qtdAnalistas = 2,
    string $avaliacao = 'ANALISTA',
): array {
    $agenda = AgendaInterlabFactory::new()->create(['status' => $status]);
    $agenda->load('interlab');
    $agenda->interlab->update([
        'tag' => 'PEP',
        'avaliacao' => $avaliacao,
    ]);

    $pessoa = PessoaFactory::new()->create(['email' => 'responsavel@example.com']);
    $empresa = PessoaFactory::new()->create();
    $endereco = Endereco::query()->create(['pessoa_id' => $empresa->id]);
    $laboratorio = InterlabLaboratorio::query()->create([
        'empresa_id' => $empresa->id,
        'endereco_id' => $endereco->id,
        'nome' => 'Laboratório Teste',
    ]);

    $inscrito = InterlabInscritoFactory::new()->create([
        'agenda_interlab_id' => $agenda->id,
        'pessoa_id' => $pessoa->id,
        'empresa_id' => $empresa->id,
        'laboratorio_id' => $laboratorio->id,
        'email' => 'lab@example.com',
        'tag_senha' => null,
    ]);

    $analistas = [];
    for ($i = 1; $i <= $qtdAnalistas; $i++) {
        $analistas[] = InterlabAnalista::query()->create([
            'interlab_inscrito_id' => $inscrito->id,
            'nome' => "Analista {$i}",
            'email' => "analista{$i}@example.com",
            'telefone' => '1199999999'.$i,
            'tag_senha' => "PEP{$i}111",
            'senha_enviada' => null,
        ]);
    }

    return [
        'agenda' => $agenda->fresh(['interlab']),
        'inscrito' => $inscrito->fresh(['analistas', 'laboratorio', 'empresa', 'pessoa']),
        'analistas' => $analistas,
    ];
}

test('action cria carta senha do analista sem enviar email', function () {
    Mail::fake();
    Queue::fake();

    ['inscrito' => $inscrito, 'analistas' => $analistas] = criarInscricaoComAnalistas();
    $analista = $analistas[0];

    $dadosDoc = app(CriarCartaSenhaAnalistaAction::class)->execute($inscrito, $analista);

    expect($dadosDoc->tipo)->toBe('tag_senha_analista')
        ->and($dadosDoc->content['analista_id'])->toBe($analista->id)
        ->and($dadosDoc->content['participante_id'])->toBe($inscrito->id)
        ->and($dadosDoc->content['tag_senha'])->toBe($analista->tag_senha);

    Queue::assertNothingPushed();
    Mail::assertNothingSent();
    expect($analista->fresh()->senha_enviada)->toBeNull();
});

test('baixar cartas senha gera um documento por analista e dispara evento com urls', function () {
    Mail::fake();
    Queue::fake();

    /** @var User $user */
    $user = User::factory()->createOne();
    $this->actingAs($user);

    ['agenda' => $agenda, 'inscrito' => $inscrito, 'analistas' => $analistas] = criarInscricaoComAnalistas();

    Livewire::test(ListParticipantes::class, [
        'idinterlab' => $agenda->id,
        'agendainterlab' => $agenda,
    ])
        ->call('baixarCartasSenha', $inscrito->id)
        ->assertDispatched('baixar-cartas-senha', function (string $eventName, array $params) use ($analistas) {
            $urls = $params['urls'] ?? [];

            return count($urls) === count($analistas)
                && collect($urls)->every(fn (string $url) => str_contains($url, '/dados-doc/'));
        });

    $docs = DadosGeraDoc::query()
        ->where('tipo', 'tag_senha_analista')
        ->where('content->participante_id', $inscrito->id)
        ->get();

    expect($docs)->toHaveCount(2);
    expect($docs->pluck('content.analista_id')->sort()->values()->all())
        ->toBe(collect($analistas)->pluck('id')->sort()->values()->all());

    Queue::assertNotPushed(EnviaSenhaAnalistaJob::class);
    Mail::assertNothingSent();
    expect($analistas[0]->fresh()->senha_enviada)->toBeNull();
    expect($analistas[1]->fresh()->senha_enviada)->toBeNull();
});

test('segunda chamada reutiliza documentos existentes sem duplicar', function () {
    Mail::fake();
    Queue::fake();

    /** @var User $user */
    $user = User::factory()->createOne();
    $this->actingAs($user);

    ['agenda' => $agenda, 'inscrito' => $inscrito] = criarInscricaoComAnalistas();

    $component = Livewire::test(ListParticipantes::class, [
        'idinterlab' => $agenda->id,
        'agendainterlab' => $agenda,
    ]);

    $component->call('baixarCartasSenha', $inscrito->id);
    $component->call('baixarCartasSenha', $inscrito->id);

    expect(
        DadosGeraDoc::query()
            ->where('tipo', 'tag_senha_analista')
            ->where('content->participante_id', $inscrito->id)
            ->count()
    )->toBe(2);

    Queue::assertNothingPushed();
});

test('inscricao sem analistas nao exibe botao de carta senha', function () {
    /** @var User $user */
    $user = User::factory()->createOne();
    $this->actingAs($user);

    ['agenda' => $agenda, 'inscrito' => $inscrito] = criarInscricaoComAnalistas(qtdAnalistas: 0);

    Livewire::test(ListParticipantes::class, [
        'idinterlab' => $agenda->id,
        'agendainterlab' => $agenda,
    ])
        ->assertDontSee('Baixar Carta Senha')
        ->call('baixarCartasSenha', $inscrito->id)
        ->assertNotDispatched('baixar-cartas-senha')
        ->assertDispatched('show-error-alert');
});

test('avaliacao laboratorial mantem link unico da carta senha do laboratorio', function () {
    /** @var User $user */
    $user = User::factory()->createOne();
    $this->actingAs($user);

    ['agenda' => $agenda, 'inscrito' => $inscrito] = criarInscricaoComAnalistas(
        avaliacao: 'LABORATORIAL',
        qtdAnalistas: 0,
    );

    $inscrito->update(['tag_senha' => 'PEPLAB1']);

    $dadosDoc = DadosGeraDoc::query()->create([
        'tipo' => 'tag_senha',
        'content' => [
            'participante_id' => $inscrito->id,
            'tag_senha' => 'PEPLAB1',
            'laboratorio_nome' => 'Laboratório Teste',
            'interlab_nome' => 'PEP Teste',
        ],
    ]);

    Livewire::test(ListParticipantes::class, [
        'idinterlab' => $agenda->id,
        'agendainterlab' => $agenda,
    ])
        ->assertSee('Baixar Carta Senha')
        ->assertSee(route('dados-doc.download', ['link' => $dadosDoc->link]), false);
});
