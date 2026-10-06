<?php

use App\Actions\Interlab\CriarInterlabLotePostagemAction;
use App\Enums\InterlabLotePostagemItemStatus;
use App\Enums\InterlabLotePostagemStatus;
use App\Livewire\Interlab\GerarLotePostagem;
use App\Models\AgendaInterlab;
use App\Models\Endereco;
use App\Models\InterlabInscrito;
use App\Models\InterlabLaboratorio;
use App\Models\Permission;
use App\Models\User;
use Database\Factories\AgendaInterlabFactory;
use Database\Factories\InterlabInscritoFactory;
use Database\Factories\InterlabLotePostagemFactory;
use Database\Factories\InterlabLotePostagemItemFactory;
use Database\Factories\PessoaFactory;
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
        ->assertSeeLivewire(GerarLotePostagem::class);
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
        ->assertSee('Falha de teste')
        ->assertSee('Limpar status')
        ->assertSee('Gerar postagem')
        ->call('dispensarErro')
        ->assertDontSee('Lote com falha')
        ->assertDontSee('Limpar status')
        ->assertSee('Gerar postagem')
        ->assertSee('Laboratórios inscritos');
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
        ->assertSee('Lote novo concluido')
        ->assertSee('Lotes gerados')
        ->assertSee('Baixar documentos (.zip)');
});

/**
 * @param  array<string, mixed>  $overrides
 */
function criarInscritoParaPostagem(array $overrides = []): InterlabInscrito
{
    $agenda = $overrides['agenda'] ?? AgendaInterlabFactory::new()->create();
    $empresa = PessoaFactory::new()->create(['tipo_pessoa' => 'PJ', 'nome_razao' => 'Empresa Teste']);
    $pessoa = PessoaFactory::new()->create(['tipo_pessoa' => 'PF']);

    $endereco = Endereco::query()->create([
        'pessoa_id' => $empresa->id,
        'info' => 'Laboratório Interlab',
        'cep' => $overrides['cep'] ?? '01001000',
        'endereco' => $overrides['endereco'] ?? 'Praça da Sé, 100',
        'complemento' => $overrides['complemento'] ?? null,
        'bairro' => $overrides['bairro'] ?? 'Sé',
        'cidade' => $overrides['cidade'] ?? 'São Paulo',
        'uf' => $overrides['uf'] ?? 'SP',
    ]);

    $laboratorio = InterlabLaboratorio::query()->create([
        'empresa_id' => $empresa->id,
        'endereco_id' => $endereco->id,
        'nome' => $overrides['nome'] ?? 'Laboratório Teste',
    ]);

    return InterlabInscritoFactory::new()->create([
        'agenda_interlab_id' => $agenda->id,
        'pessoa_id' => $pessoa->id,
        'empresa_id' => $empresa->id,
        'laboratorio_id' => $laboratorio->id,
        'informacoes_inscricao' => $overrides['informacoes_inscricao'] ?? 'Amostra padrão',
    ])->load('agendaInterlab');
}

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
