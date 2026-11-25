<?php

use App\Actions\Interlab\CriarInterlabLotePostagemAction;
use App\Enums\InterlabLotePostagemItemStatus;
use App\Enums\InterlabLotePostagemStatus;
use App\Jobs\Interlab\ClassificarServicoLotePostagemJob;
use App\Livewire\Interlab\GerarLotePostagem;
use App\Livewire\Interlab\ItensPostados;
use App\Models\AgendaInterlab;
use App\Models\Permission;
use App\Models\User;
use Database\Factories\AgendaInterlabFactory;
use Database\Factories\InterlabInscritoFactory;
use Database\Factories\InterlabLotePostagemFactory;
use Database\Factories\InterlabLotePostagemItemFactory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

test('nao preenche dados da postagem fora do ambiente local', function () {
    $inscrito = criarInscritoParaPostagem();

    Livewire::test(GerarLotePostagem::class, ['agenda' => $inscrito->agendaInterlab])
        ->assertSet('form.nome', '')
        ->assertSet('form.pesoGramas', '')
        ->assertSet('form.altura', null)
        ->assertSet('form.largura', null)
        ->assertSet('form.comprimento', null)
        ->assertSet('form.itensDeclaracao', [[
            'conteudo' => '',
            'quantidade' => '',
            'valor_unitario' => '',
        ]]);
});

test('preenche dados da postagem no ambiente local', function () {
    $this->app['env'] = 'local';

    $inscrito = criarInscritoParaPostagem();

    Livewire::test(GerarLotePostagem::class, ['agenda' => $inscrito->agendaInterlab])
        ->assertSet('form.nome', 'Amostras')
        ->assertSet('form.pesoGramas', '1000')
        ->assertSet('form.altura', '20')
        ->assertSet('form.largura', '40')
        ->assertSet('form.comprimento', '40')
        ->assertSet('form.itensDeclaracao', [[
            'conteudo' => 'Amostras',
            'quantidade' => '1',
            'valor_unitario' => '1,00',
        ]]);
});

test('lista laboratorio com endereco e informacoes da inscricao', function () {
    $inscrito = criarInscritoParaPostagem([
        'nome' => 'Laboratório Alfa',
        'informacoes_inscricao' => 'Enviar amostra refrigerada',
        'endereco' => 'Rua das Flores, 100',
        'bairro' => 'Centro',
        'cidade' => 'Porto Alegre',
        'cep' => '90010000',
    ]);

    Livewire::test(GerarLotePostagem::class, ['agenda' => $inscrito->agendaInterlab])
        ->assertSee('Laboratório Alfa')
        ->assertSee('Rua das Flores')
        ->assertSee('Centro')
        ->assertSee('Porto Alegre')
        ->assertSee('90010-000')
        ->assertSee('Enviar amostra refrigerada')
        ->assertSeeHtml('value="'.$inscrito->id.'"')
        ->assertSee('Gerar postagem')
        ->assertSeeHtml('wire:model="inscritosSelecionados"')
        ->assertDontSee('wire:model.live', false)
        ->assertDontSee('wire:click="adicionarItemDeclaracao"', false)
        ->assertSee('Formato: Caixa/pacote')
        ->assertSee('Altura (cm)')
        ->assertSee('Largura (cm)')
        ->assertSee('Comprimento (cm)')
        ->assertDontSee('name="form.codigoFormato"', false);
});

test('omite inscricao sem laboratorio', function () {
    $comLaboratorio = criarInscritoParaPostagem([
        'nome' => 'Laboratório Visível',
        'informacoes_inscricao' => 'Destino válido',
    ]);
    InterlabInscritoFactory::new()->create([
        'agenda_interlab_id' => $comLaboratorio->agenda_interlab_id,
        'laboratorio_id' => null,
        'informacoes_inscricao' => 'Inscrição sem destinatário',
    ]);

    Livewire::test(GerarLotePostagem::class, ['agenda' => $comLaboratorio->agendaInterlab])
        ->assertSee('Laboratório Visível')
        ->assertDontSee('Inscrição sem destinatário');
});

test('checkbox atualiza os laboratorios selecionados', function () {
    $inscrito = criarInscritoParaPostagem();

    Livewire::test(GerarLotePostagem::class, ['agenda' => $inscrito->agendaInterlab])
        ->set('inscritosSelecionados', [$inscrito->id])
        ->assertSet('inscritosSelecionados', [$inscrito->id]);
});

test('recusa gerar postagem sem laboratorio selecionado', function () {
    $inscrito = criarInscritoParaPostagem();
    $action = $this->mock(CriarInterlabLotePostagemAction::class);
    $action->shouldReceive('execute')->never();

    preencherLotePostagem(Livewire::test(GerarLotePostagem::class, ['agenda' => $inscrito->agendaInterlab]))
        ->call('gerarPostagem')
        ->assertHasErrors(['inscritosSelecionados' => 'Selecione ao menos um laboratório.'])
        ->assertDispatched('show-error-alert', message: 'Selecione ao menos um laboratório.')
        ->assertSeeHtml('id="erros-gerar-postagem"');
});

test('recusa declaracao com conteudo curto', function () {
    $inscrito = criarInscritoParaPostagem();

    preencherLotePostagem(Livewire::test(GerarLotePostagem::class, ['agenda' => $inscrito->agendaInterlab]), $inscrito->id)
        ->set('form.itensDeclaracao.0.conteudo', 'Ab')
        ->call('gerarPostagem')
        ->assertHasErrors(['form.itensDeclaracao.0.conteudo' => 'O conteúdo deve ter no mínimo 5 caracteres.']);
});

test('recusa peso invalido', function () {
    $inscrito = criarInscritoParaPostagem();

    preencherLotePostagem(Livewire::test(GerarLotePostagem::class, ['agenda' => $inscrito->agendaInterlab]), $inscrito->id)
        ->set('form.pesoGramas', '0')
        ->call('gerarPostagem')
        ->assertHasErrors(['form.pesoGramas' => 'O peso deve ser um número inteiro maior que zero, com até 6 dígitos.']);
});

test('recusa caixa sem dimensoes', function () {
    $inscrito = criarInscritoParaPostagem();

    preencherLotePostagem(Livewire::test(GerarLotePostagem::class, ['agenda' => $inscrito->agendaInterlab]), $inscrito->id)
        ->set('form.altura', '')
        ->set('form.largura', '')
        ->set('form.comprimento', '')
        ->call('gerarPostagem')
        ->assertHasErrors([
            'form.altura' => 'Informe a altura da caixa, em centímetros.',
            'form.largura' => 'Informe a largura da caixa, em centímetros.',
            'form.comprimento' => 'Informe o comprimento da caixa, em centímetros.',
        ]);
});

test('gera postagem com os parametros compartilhados dos laboratorios selecionados', function () {
    $inscrito = criarInscritoParaPostagem();
    $action = $this->mock(CriarInterlabLotePostagemAction::class);
    $action->shouldReceive('execute')
        ->once()
        ->withArgs(function (AgendaInterlab $agenda, array $ids, array $parametros) use ($inscrito): bool {
            return $agenda->is($inscrito->agendaInterlab)
                && $ids === [$inscrito->id]
                && $parametros === [
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
        });

    preencherLotePostagem(Livewire::test(GerarLotePostagem::class, ['agenda' => $inscrito->agendaInterlab]), $inscrito->id)
        ->call('gerarPostagem')
        ->assertHasNoErrors()
        ->assertDispatched('show-success-alert');
});

test('recusa laboratorio com endereco incompleto', function () {
    $inscrito = criarInscritoParaPostagem([
        'nome' => 'Laboratório Sem Bairro',
        'bairro' => '',
    ]);
    $action = $this->mock(CriarInterlabLotePostagemAction::class);
    $action->shouldReceive('execute')->never();

    preencherLotePostagem(Livewire::test(GerarLotePostagem::class, ['agenda' => $inscrito->agendaInterlab]), $inscrito->id)
        ->call('gerarPostagem')
        ->assertHasErrors(['inscritosSelecionados' => 'Informe o bairro do laboratório Laboratório Sem Bairro.']);
});

test('consulta todos os ceps e mostra recusas por laboratorio sem criar o lote', function (bool $consultaIndisponivel) {
    configurarPrePostagemCorreios();
    Queue::fake();

    $primeiro = criarInscritoParaPostagem(['nome' => 'Laboratório Alfa', 'cep' => '98900000']);
    $inscritos = collect([$primeiro]);
    foreach (['01001000', '90010000', '92990000', '93010050', '32206240', '98900000'] as $indice => $cep) {
        $inscritos->push(criarInscritoParaPostagem([
            'agenda' => $primeiro->agendaInterlab,
            'nome' => 'Laboratório '.($indice + 2),
            'cep' => $cep,
        ]));
    }

    Http::fake([
        '*/token/v1/autentica/contrato' => respostaTokenCorreios(),
        '*/cep/v2/enderecos/*' => function (Request $request) use ($consultaIndisponivel) {
            $cep = basename($request->url());

            if ($consultaIndisponivel && $cep === '92990000') {
                return Http::response([], 503);
            }

            return in_array($cep, ['98900000', '32206240'], true)
                ? Http::response(['cep' => $cep, 'txMsg' => "CEP-003: O CEP {$cep} não foi encontrado"], 404)
                : Http::response(['cep' => $cep]);
        },
    ]);

    $componente = preencherLotePostagem(Livewire::test(GerarLotePostagem::class, ['agenda' => $primeiro->agendaInterlab]))
        ->set('inscritosSelecionados', $inscritos->pluck('id')->all())
        ->call('gerarPostagem')
        ->assertDispatched('show-error-alert')
        ->assertNotDispatched('show-success-alert')
        ->assertSee('98900-000 do laboratório Laboratório Alfa')
        ->assertSee('32206-240 do laboratório Laboratório 6')
        ->assertSee('98900-000 do laboratório Laboratório 7')
        ->assertSet('inscritosSelecionados', $inscritos->pluck('id')->all());

    if ($consultaIndisponivel) {
        $componente->assertSee('A validação de CEPs ficou incompleta. Tente novamente.')
            ->assertDontSee('92990-000 do laboratório');
    }

    expect(Http::recorded()->filter(fn (array $par): bool => str_contains($par[0]->url(), '/cep/v2/enderecos/')))->toHaveCount(6);
    $this->assertDatabaseCount('interlab_lote_postagens', 0);
    $this->assertDatabaseCount('interlab_lote_postagem_itens', 0);
    Queue::assertNotPushed(ClassificarServicoLotePostagemJob::class);
})->with([
    'respostas completas' => false,
    'consulta indisponível junto com recusas' => true,
]);

test('recusa selecionar o mesmo laboratorio mais de uma vez', function () {
    $inscrito = criarInscritoParaPostagem(['nome' => 'Laboratório Duplicado']);
    $repetido = InterlabInscritoFactory::new()->create([
        'agenda_interlab_id' => $inscrito->agenda_interlab_id,
        'pessoa_id' => $inscrito->pessoa_id,
        'empresa_id' => $inscrito->empresa_id,
        'laboratorio_id' => $inscrito->laboratorio_id,
        'informacoes_inscricao' => 'Segunda inscrição do mesmo laboratório',
    ]);
    $action = $this->mock(CriarInterlabLotePostagemAction::class);
    $action->shouldReceive('execute')->never();

    preencherLotePostagem(Livewire::test(GerarLotePostagem::class, ['agenda' => $inscrito->agendaInterlab]))
        ->set('inscritosSelecionados', [$inscrito->id, $repetido->id])
        ->call('gerarPostagem')
        ->assertHasErrors(['inscritosSelecionados' => 'Um laboratório só pode ser selecionado uma vez no mesmo lote.']);
});

test('nao envia inscricao de outra agenda para a geracao', function () {
    $inscrito = criarInscritoParaPostagem();
    $outraAgenda = criarInscritoParaPostagem(['nome' => 'Laboratório de outra agenda']);
    $action = $this->mock(CriarInterlabLotePostagemAction::class);
    $action->shouldReceive('execute')->never();

    preencherLotePostagem(
        Livewire::test(GerarLotePostagem::class, ['agenda' => $inscrito->agendaInterlab]),
        $inscrito->id,
    )
        ->set('inscritosSelecionados', [$inscrito->id, $outraAgenda->id])
        ->call('gerarPostagem')
        ->assertHasErrors(['inscritosSelecionados' => 'A seleção contém inscrições que não pertencem a esta agenda.']);
});

test('action recusa inscricao que nao pertence a agenda', function () {
    $inscrito = criarInscritoParaPostagem();
    $outraAgenda = criarInscritoParaPostagem();

    expect(fn () => app(CriarInterlabLotePostagemAction::class)->execute(
        $inscrito->agendaInterlab,
        [$outraAgenda->id],
        ['nome' => 'Lote setembro'],
    ))->toThrow(ValidationException::class, 'não pertencem a esta agenda');
});

test('exibe a aba de postagens na agenda', function () {
    $user = User::factory()->create();
    $permission = Permission::withoutEvents(fn (): Permission => Permission::query()->firstOrCreate([
        'permission' => 'funcionario',
    ]));
    $user->permissions()->syncWithoutDetaching([$permission->id]);

    $agenda = AgendaInterlabFactory::new()->create();

    $this->actingAs($user)
        ->get(route('agenda-interlab-insert', $agenda->uid))
        ->assertOk()
        ->assertSee('Postagens')
        ->assertSeeLivewire(GerarLotePostagem::class)
        ->assertSee('Itens Postados')
        ->assertSeeLivewire(ItensPostados::class);
});

test('lote ativo oculta laboratorios e formulario', function () {
    $inscrito = criarInscritoParaPostagem();
    InterlabLotePostagemFactory::new()->create([
        'agenda_interlab_id' => $inscrito->agenda_interlab_id,
        'status' => InterlabLotePostagemStatus::GerandoEtiquetas,
        'nome' => 'Lote em andamento',
        'ultima_progressao_em' => now(),
    ]);

    Livewire::test(GerarLotePostagem::class, ['agenda' => $inscrito->agendaInterlab->fresh()])
        ->assertSee('Lote em andamento')
        ->assertSeeHtml('wire:poll.5s')
        ->assertDontSee('Laboratórios inscritos')
        ->assertDontSee('Gerar postagem');
});

test('lote com erro mostra limpar status e dispensa o alerta', function () {
    $inscrito = criarInscritoParaPostagem();
    $lote = InterlabLotePostagemFactory::new()->create([
        'agenda_interlab_id' => $inscrito->agenda_interlab_id,
        'status' => InterlabLotePostagemStatus::Erro,
        'etapa_erro' => InterlabLotePostagemStatus::GerandoEtiquetas->value,
        'erro_mensagem' => 'Falha de teste',
        'nome' => 'Lote com falha',
    ]);

    InterlabLotePostagemItemFactory::new()->create([
        'interlab_lote_postagem_id' => $lote->id,
        'status' => InterlabLotePostagemItemStatus::Erro,
        'destinatario' => [
            'nome' => 'Lab Falha',
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

    Livewire::test(GerarLotePostagem::class, ['agenda' => $inscrito->agendaInterlab->fresh()])
        ->assertSee('Lote com falha')
        ->assertSee('Não foi possível concluir a postagem do lote')
        ->assertDontSee('Falha de teste')
        ->assertDontSee('Lab Falha')
        ->assertSee('Limpar status')
        ->assertSee('Gerar postagem')
        ->call('dispensarErro', $lote->uid)
        ->assertDontSee('Lote com falha')
        ->assertDontSee('Limpar status')
        ->assertSee('Gerar postagem')
        ->assertSee('Laboratórios inscritos');
});

test('avisa a falha uma vez quando o lote ativo encerra durante a consulta da tela', function () {
    $inscrito = criarInscritoParaPostagem();
    $lote = InterlabLotePostagemFactory::new()->create([
        'agenda_interlab_id' => $inscrito->agenda_interlab_id,
        'status' => InterlabLotePostagemStatus::GerandoPrePostagens,
        'nome' => 'Lote em andamento',
    ]);

    $componente = Livewire::test(GerarLotePostagem::class, ['agenda' => $inscrito->agendaInterlab])
        ->assertNotDispatched('show-error-alert');

    $lote->update([
        'status' => InterlabLotePostagemStatus::Erro,
        'etapa_erro' => InterlabLotePostagemStatus::GerandoPrePostagens->value,
        'erro_mensagem' => 'Detalhe interno da API',
    ]);

    $componente->call('$refresh')
        ->assertDispatched('show-error-alert', message: 'Não foi possível concluir a postagem do lote Lote em andamento.')
        ->assertDontSee('Detalhe interno da API')
        ->call('$refresh')
        ->assertNotDispatched('show-error-alert');
});

test('nao mostra erro antigo quando ha lote concluido mais recente', function () {
    $inscrito = criarInscritoParaPostagem();

    InterlabLotePostagemFactory::new()->create([
        'agenda_interlab_id' => $inscrito->agenda_interlab_id,
        'status' => InterlabLotePostagemStatus::Erro,
        'etapa_erro' => InterlabLotePostagemStatus::GerandoEtiquetas->value,
        'erro_mensagem' => 'Erro antigo',
        'nome' => 'Lote antigo com erro',
    ]);

    InterlabLotePostagemFactory::new()->create([
        'agenda_interlab_id' => $inscrito->agenda_interlab_id,
        'status' => InterlabLotePostagemStatus::Concluido,
        'nome' => 'Lote novo concluido',
        'declaracao_conteudo_pdf_path' => 'correios/declaracoes/novo.pdf',
    ]);

    Livewire::test(GerarLotePostagem::class, ['agenda' => $inscrito->agendaInterlab->fresh()])
        ->assertDontSee('Erro antigo')
        ->assertDontSee('Lote antigo com erro')
        ->assertDontSee('Limpar status')
        ->assertDontSee('Lotes gerados')
        ->assertDontSee('Baixar documentos (.zip)');
});

function preencherLotePostagem(Testable $component, ?int $inscritoId = null): Testable
{
    if ($inscritoId !== null) {
        $component->set('inscritosSelecionados', [$inscritoId]);
    }

    return $component
        ->set('form.nome', 'Lote setembro')
        ->set('form.altura', '10')
        ->set('form.largura', '15')
        ->set('form.comprimento', '20')
        ->set('form.pesoGramas', '500')
        ->set('form.itensDeclaracao', [[
            'conteudo' => 'Amostra para ensaio interlaboratorial',
            'quantidade' => '1',
            'valor_unitario' => '50,00',
        ]]);
}
