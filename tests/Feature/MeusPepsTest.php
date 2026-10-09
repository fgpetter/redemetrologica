<?php

use App\Models\AgendaInterlab;
use App\Models\AgendainterlabMaterial;
use App\Models\Endereco;
use App\Models\InterlabInscrito;
use App\Models\InterlabLaboratorio;
use App\Models\Permission;
use App\Models\User;
use Database\Factories\AgendaInterlabFactory;
use Database\Factories\InterlabFactory;
use Database\Factories\InterlabInscritoFactory;
use Database\Factories\PessoaFactory;

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
        ->assertSee('Materiais de apoio para o interlab')
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
