<?php

use App\Livewire\PainelCliente\MeusPeps;
use App\Models\AgendaInterlab;
use App\Models\AgendainterlabMaterial;
use App\Models\DadosGeraDoc;
use App\Models\Endereco;
use App\Models\InterlabAnalista;
use App\Models\InterlabInscrito;
use App\Models\InterlabLaboratorio;
use App\Models\Permission;
use App\Models\User;
use Database\Factories\AgendaInterlabFactory;
use Database\Factories\InterlabFactory;
use Database\Factories\InterlabInscritoFactory;
use Database\Factories\PessoaFactory;
use Livewire\Livewire;

function usuarioClienteComPessoa(): User
{
    $user = User::factory()->create();
    $permission = Permission::withoutEvents(function (): Permission {
        return Permission::query()->firstOrCreate(['permission' => 'cliente']);
    });
    $user->permissions()->syncWithoutDetaching([$permission->id]);
    PessoaFactory::new()->create(['user_id' => $user->id]);

    return $user;
}

function inscreverPessoaNoPep(User $user, AgendaInterlab $agenda, string $nomeLaboratorio): InterlabInscrito
{
    $empresa = PessoaFactory::new()->create();
    $endereco = Endereco::query()->create(['pessoa_id' => $empresa->id]);
    $laboratorio = InterlabLaboratorio::query()->create([
        'empresa_id' => $empresa->id,
        'endereco_id' => $endereco->id,
        'nome' => $nomeLaboratorio,
    ]);

    return InterlabInscritoFactory::new()->create([
        'agenda_interlab_id' => $agenda->id,
        'pessoa_id' => $user->pessoa->id,
        'empresa_id' => $empresa->id,
        'laboratorio_id' => $laboratorio->id,
    ]);
}

function agendaComNome(string $nomeInterlab, string $status = 'CONFIRMADO'): AgendaInterlab
{
    return AgendaInterlabFactory::new()->create([
        'interlab_id' => InterlabFactory::new()->create(['nome' => $nomeInterlab]),
        'status' => $status,
    ]);
}

test('painel mostra apenas nome do PEP e link para Meus PEPs', function () {
    $user = usuarioClienteComPessoa();
    $agenda = agendaComNome('PEP Ativo');
    inscreverPessoaNoPep($user, $agenda, 'Laboratório Oculto');
    AgendainterlabMaterial::query()->create([
        'agenda_interlab_id' => $agenda->id,
        'arquivo' => 'material-oculto.pdf',
        'descricao' => 'Material oculto',
    ]);

    $this->actingAs($user)
        ->get(route('painel-index'))
        ->assertOk()
        ->assertSee('PEP Ativo')
        ->assertSee('ver detalhes')
        ->assertSee(route('painel-meus-peps').'#agenda-'.$agenda->id)
        ->assertDontSee('Laboratório Oculto')
        ->assertDontSee('Material oculto');
});

test('painel lista PEP concluído com selo Concluído', function () {
    $user = usuarioClienteComPessoa();
    $agenda = agendaComNome('PEP Encerrado', 'CONCLUIDO');
    inscreverPessoaNoPep($user, $agenda, 'Laboratório Encerrado');

    $this->actingAs($user)
        ->get(route('painel-index'))
        ->assertOk()
        ->assertSee('PEP Encerrado')
        ->assertSee('Concluído')
        ->assertSee(route('painel-meus-peps').'#agenda-'.$agenda->id);
});

test('painel não exibe selo Concluído em PEP ativo', function () {
    $user = usuarioClienteComPessoa();
    $agenda = agendaComNome('PEP Em Andamento');
    inscreverPessoaNoPep($user, $agenda, 'Laboratório Ativo');

    $this->actingAs($user)
        ->get(route('painel-index'))
        ->assertOk()
        ->assertSee('PEP Em Andamento')
        ->assertDontSee('Concluído');
});

test('meus peps lista PEPs encerrados com dados do laboratório e materiais de apoio', function () {
    $user = usuarioClienteComPessoa();
    $agenda = agendaComNome('PEP Encerrado', 'CONCLUIDO');
    inscreverPessoaNoPep($user, $agenda, 'Laboratório Visível');
    AgendainterlabMaterial::query()->create([
        'agenda_interlab_id' => $agenda->id,
        'arquivo' => 'material-apoio.pdf',
        'descricao' => 'Material de apoio do PEP',
    ]);

    $this->actingAs($user)
        ->get(route('painel-meus-peps'))
        ->assertOk()
        ->assertSee('PEP Encerrado')
        ->assertSee('CONCLUIDO')
        ->assertSee('Laboratório Visível')
        ->assertSee('Materiais do Interlab')
        ->assertSee('Material de apoio do PEP')
        ->assertSee('id="agenda-'.$agenda->id.'"', false);
});

test('meus peps não exibe inscrições de outra pessoa', function () {
    $user = usuarioClienteComPessoa();
    $outroUsuario = usuarioClienteComPessoa();
    $agenda = agendaComNome('PEP de Outro');
    inscreverPessoaNoPep($outroUsuario, $agenda, 'Laboratório de Outro');

    $this->actingAs($user)
        ->get(route('painel-meus-peps'))
        ->assertOk()
        ->assertDontSee('PEP de Outro')
        ->assertDontSee('Laboratório de Outro');
});

test('meus peps retorna 404 para usuário sem permissão de cliente', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('painel-meus-peps'))
        ->assertNotFound();
});

test('documentos existentes são associados à inscrição e ao analista corretos sem criar registros', function () {
    $user = usuarioClienteComPessoa();
    $outroUsuario = usuarioClienteComPessoa();
    $agenda = agendaComNome('PEP Documentos');
    $inscrito = inscreverPessoaNoPep($user, $agenda, 'Laboratório');
    $outro = inscreverPessoaNoPep($outroUsuario, $agenda, 'Outro');
    $analista = InterlabAnalista::create(['interlab_inscrito_id' => $inscrito->id, 'nome' => 'Analista próprio', 'email' => 'proprio@example.com', 'telefone' => '51999999999']);
    $analistaOutro = InterlabAnalista::create(['interlab_inscrito_id' => $outro->id, 'nome' => 'Analista externo', 'email' => 'externo@example.com', 'telefone' => '51999999999']);
    $doc = fn (string $tipo, array $content) => DadosGeraDoc::create(compact('tipo', 'content'));
    $cartaAntiga = $doc('tag_senha', ['participante_id' => $inscrito->id]);
    $carta = $doc('tag_senha', ['participante_id' => $inscrito->id]);
    $certificado = $doc('certificado_interlab', ['participante_id' => $inscrito->id]);
    $cartaAnalista = $doc('tag_senha_analista', ['participante_id' => $inscrito->id, 'analista_id' => $analista->id]);
    $cartaOutro = $doc('tag_senha_analista', ['participante_id' => $outro->id, 'analista_id' => $analistaOutro->id]);
    $doc('tag_senha_analista', ['participante_id' => $inscrito->id, 'analista_id' => $analistaOutro->id]);
    $quantidade = DadosGeraDoc::count();

    $this->actingAs($user);
    Livewire::test(MeusPeps::class)
        ->assertViewHas('documentosPorInscricao', fn ($docs) => $docs === [
            $inscrito->id => [
                'carta' => route('dados-doc.download', $carta->link),
                'certificado' => null,
                'analistas' => [['nome' => 'Analista próprio', 'carta' => route('dados-doc.download', $cartaAnalista->link), 'certificado' => null]],
            ],
        ])
        ->assertDontSee($cartaOutro->link)
        ->assertDontSee($cartaAntiga->link);

    $inscrito->update(['certificado_emitido' => now()]);
    Livewire::test(MeusPeps::class)
        ->assertViewHas('documentosPorInscricao', fn ($docs) => $docs[$inscrito->id]['certificado'] === route('dados-doc.download', $certificado->link));
    expect(DadosGeraDoc::count())->toBe($quantidade);
});

test('inscrição sem documentos mantém URLs nulas', function () {
    $user = usuarioClienteComPessoa();
    $inscrito = inscreverPessoaNoPep($user, agendaComNome('Sem documentos'), 'Laboratório');
    $inscrito->update(['certificado_emitido' => now()]);

    $this->actingAs($user);
    Livewire::test(MeusPeps::class)->assertViewHas('documentosPorInscricao', fn ($docs) => $docs[$inscrito->id] === [
        'carta' => null, 'certificado' => null, 'analistas' => [],
    ]);
});

test('refresh isola analistas de inscrições em laboratório compartilhado e revalida o proprietário', function () {
    $user = usuarioClienteComPessoa();
    $outroUsuario = usuarioClienteComPessoa();
    $agenda = agendaComNome('PEP compartilhado');
    $inscrito = inscreverPessoaNoPep($user, $agenda, 'Laboratório compartilhado');
    $outro = inscreverPessoaNoPep($outroUsuario, $agenda, 'Laboratório externo');
    $outro->update(['laboratorio_id' => $inscrito->laboratorio_id]);
    InterlabAnalista::create(['interlab_inscrito_id' => $inscrito->id, 'nome' => 'Analista autorizado', 'email' => 'autorizado@example.com', 'telefone' => '51999999999']);
    InterlabAnalista::create(['interlab_inscrito_id' => $outro->id, 'nome' => 'Analista privado', 'email' => 'privado@example.com', 'telefone' => '51988888888']);
    $doc = DadosGeraDoc::create(['tipo' => 'tag_senha', 'content' => ['participante_id' => $inscrito->id]]);

    $this->actingAs($user);
    $component = Livewire::test(MeusPeps::class)
        ->assertSee('Analista autorizado')
        ->assertDontSee('Analista privado')
        ->call('$refresh')
        ->assertSee('Analista autorizado')
        ->assertDontSee('Analista privado')
        ->assertDontSee('privado@example.com')
        ->assertSee($doc->link);

    $inscrito->update(['pessoa_id' => $outroUsuario->pessoa->id]);
    $component->call('$refresh')
        ->assertDontSee('Analista autorizado')
        ->assertDontSee('PEP compartilhado')
        ->assertDontSee('Laboratório compartilhado')
        ->assertDontSee($doc->link)
        ->assertViewHas('interlabs', fn ($interlabs) => $interlabs->isEmpty())
        ->assertViewHas('documentosPorInscricao', []);
});
