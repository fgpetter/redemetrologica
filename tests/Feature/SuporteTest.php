<?php

use App\Actions\Suporte\SalvarDemandaSuporteAction;
use App\Enums\SuporteClassificacao;
use App\Enums\SuporteStatus;
use App\Livewire\Suporte\AtividadeForm;
use App\Livewire\Suporte\DemandaDetalhe;
use App\Livewire\Suporte\SuporteTable;
use App\Models\Permission;
use App\Models\SuporteArquivo;
use App\Models\SuporteAtividade;
use App\Models\SuporteDemanda;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

function usuarioDoSuporte(string $perfil = 'funcionario'): User
{
    $user = User::factory()->create();
    $permission = Permission::withoutEvents(fn () => Permission::query()->firstOrCreate(['permission' => $perfil]));
    $user->permissions()->syncWithoutDetaching([$permission->id]);

    return $user;
}

test('suporte exige login e perfil interno nas páginas', function () {
    $this->get(route('suporte-index'))->assertRedirect(route('login'));
    $user = usuarioDoSuporte('cliente');
    $this->actingAs($user)->get(route('suporte-index'))->assertNotFound();
    $this->get(route('suporte-insert'))->assertNotFound();
    Livewire::actingAs($user)->test(SuporteTable::class)->assertNotFound();
});

test('admins e funcionários acessam listagem e cadastro', function (string $perfil) {
    $user = usuarioDoSuporte($perfil);
    $this->actingAs($user)->get(route('suporte-index'))->assertOk()->assertSeeLivewire(SuporteTable::class)
        ->assertSeeInOrder(['CADASTROS ADICIONAIS', 'SUPORTE', 'SITE']);
    $this->get(route('suporte-insert'))->assertOk()->assertSeeLivewire(DemandaDetalhe::class);
})->with(['admin', 'funcionario']);

test('cria demanda aberta com autor e participantes e inclui no final da fila', function () {
    $user = usuarioDoSuporte();
    $participante = usuarioDoSuporte('admin');
    $anterior = SuporteDemanda::factory()->create();
    Livewire::actingAs($user)->test(DemandaDetalhe::class)
        ->set('form.titulo', 'Ajustar relatório')
        ->set('form.descricao', '<p>Incluir <strong>filtro</strong>.</p><script>alert(1)</script>')
        ->set('form.participantes', [$participante->id])
        ->call('salvar')->assertHasNoErrors();
    $demanda = SuporteDemanda::query()->where('titulo', 'Ajustar relatório')->firstOrFail();
    expect($demanda->status)->toBe(SuporteStatus::Aberta)
        ->and($demanda->autor_id)->toBe($user->id)
        ->and($demanda->prioridade)->toBe($anterior->prioridade + 1)
        ->and($demanda->descricao)->toContain('<strong>filtro</strong>')->not->toContain('<script')
        ->and($demanda->participantes()->pluck('users.id')->all())->toEqualCanonicalizing([$user->id, $participante->id]);
});

test('demanda abre em edição com atividades e preserva formulário após atualizações', function () {
    $user = usuarioDoSuporte();
    $demanda = SuporteDemanda::factory()->create();
    Livewire::actingAs($user)->test(DemandaDetalhe::class, ['demanda' => $demanda])
        ->assertSet('form.titulo', $demanda->titulo)
        ->assertSeeHtml('wire:submit="salvar"')
        ->assertDontSeeHtml('wire:click="editar"')
        ->assertSeeLivewire(AtividadeForm::class)
        ->set('form.titulo', 'Alteração em andamento')
        ->dispatch('suporte-atualizado')
        ->assertSet('form.titulo', 'Alteração em andamento')
        ->set('status', 'em_analise')->call('salvar')
        ->assertSeeHtml('wire:submit="salvar"');
});

test('demanda exibe autoria abaixo do título status após anexos e exclusão junto de salvar', function () {
    $admin = usuarioDoSuporte('admin');
    $demanda = SuporteDemanda::factory()->create(['autor_id' => $admin->id]);

    Livewire::actingAs($admin)->test(DemandaDetalhe::class, ['demanda' => $demanda])
        ->assertSeeHtml('text-black')
        ->assertSeeHtmlInOrder([
            'Criada por',
            'Usuários atrelados',
            'wire:model="form.horasGastas"',
            'Adicionar anexos',
            'name=status',
            'wire:model="form.classificacao"',
            'Salvar demanda',
            '$wire.excluir()',
        ])
        ->assertDontSee('Alterar status')
        ->assertSeeHtml('Swal.fire(')
        ->assertSee('Sim, excluir!')
        ->assertSee('Cancelar')
        ->assertDontSeeHtml('wire:confirm=');
});

test('edição carrega participantes selecionados e permite substituir e remover vínculos', function () {
    $user = usuarioDoSuporte('admin');
    $participante = usuarioDoSuporte();
    $demanda = SuporteDemanda::factory()->create(['autor_id' => $user->id]);
    $demanda->participantes()->sync([$participante->id]);

    $component = Livewire::actingAs($user)->test(DemandaDetalhe::class, ['demanda' => $demanda])
        ->assertSet('form.participantes', [$participante->id]);
    $component->set('form.participantes', [$user->id])->call('salvar')->assertHasNoErrors();
    expect($demanda->participantes()->pluck('users.id')->all())->toBe([$user->id]);

    Livewire::test(DemandaDetalhe::class, ['demanda' => $demanda])->set('form.participantes', [])->call('salvar')->assertHasNoErrors();
    expect($demanda->participantes()->count())->toBe(0);
});

test('valida título descrição vazia e participante sem perfil permitido', function () {
    $user = usuarioDoSuporte();
    $cliente = usuarioDoSuporte('cliente');
    $component = Livewire::actingAs($user)->test(DemandaDetalhe::class);
    $component->call('salvar')->assertHasErrors(['form.titulo', 'form.descricao']);
    $component->set('form.titulo', str_repeat('a', 256))->set('form.descricao', '<p>Teste</p>')
        ->call('salvar')->assertHasErrors('form.titulo');
    $component->set('form.titulo', 'Teste')->set('form.descricao', '<p>&nbsp;</p>')
        ->call('salvar')->assertHasErrors('form.descricao');
    $component->set('form.descricao', '<p>Teste</p>')->set('form.participantes', [$cliente->id])
        ->call('salvar')->assertHasErrors('form.participantes');
    expect(SuporteDemanda::count())->toBe(0);
});

test('filtro padrão exclui encerradas e prioridade se mantém nos filtros', function () {
    $user = usuarioDoSuporte();
    $primeira = SuporteDemanda::factory()->create(['titulo' => 'Primeira demanda']);
    $concluida = SuporteDemanda::factory()->concluida()->create(['titulo' => 'Demanda concluída']);
    SuporteDemanda::factory()->cancelada()->create(['titulo' => 'Demanda cancelada']);
    $segunda = SuporteDemanda::factory()->create(['titulo' => 'Segunda demanda']);
    Livewire::actingAs($user)->test(SuporteTable::class)
        ->assertSee('Todas demandas')->assertDontSeeHtml('wire:model.live="pendentes"')
        ->assertSeeHtmlInOrder([$primeira->titulo, $segunda->titulo])
        ->assertDontSee('Demanda concluída')->assertDontSee('Demanda cancelada')
        ->set('status', 'todos')->assertSeeHtmlInOrder([$primeira->titulo, $concluida->titulo, 'Demanda cancelada', $segunda->titulo])
        ->set('status', 'concluida')->assertSee($concluida->titulo)->assertDontSee($primeira->titulo);
});

test('chevrons trocam vizinhas visíveis persistindo prioridade global', function () {
    $user = usuarioDoSuporte();
    $primeira = SuporteDemanda::factory()->create(['titulo' => 'Relatório A']);
    $oculta = SuporteDemanda::factory()->create(['titulo' => 'Cadastro']);
    $ultima = SuporteDemanda::factory()->create(['titulo' => 'Relatório B']);
    Livewire::actingAs($user)->test(SuporteTable::class)->set('busca', 'Relatório')
        ->call('mover', $ultima->uid, 'subir')->assertSeeHtmlInOrder(['Relatório B', 'Relatório A'])
        ->set('busca', '')->assertSeeHtmlInOrder(['Relatório B', 'Cadastro', 'Relatório A']);
    expect($ultima->fresh()->prioridade)->toBe(1)
        ->and($oculta->fresh()->prioridade)->toBe(2)
        ->and($primeira->fresh()->prioridade)->toBe(3);
    Livewire::test(SuporteTable::class)->call('mover', $ultima->uid, 'subir')->assertHasNoErrors();
});

test('movimentação alcança vizinha em outra página sem perder filtros', function () {
    $user = usuarioDoSuporte();
    $demandas = SuporteDemanda::factory()->count(16)->create();
    $ultima = $demandas->last();
    Livewire::actingAs($user)->test(SuporteTable::class)->call('setPage', 2)
        ->assertSee($ultima->titulo)->call('mover', $ultima->uid, 'subir')->assertHasNoErrors();
    expect($ultima->fresh()->prioridade)->toBe(15)
        ->and($demandas[14]->fresh()->prioridade)->toBe(16);
});

test('bloqueia segunda demanda em andamento e permite após liberar a primeira', function () {
    $user = usuarioDoSuporte();
    $primeira = SuporteDemanda::factory()->create(['status' => SuporteStatus::EmAndamento]);
    $segunda = SuporteDemanda::factory()->create(['autor_id' => $user->id, 'titulo' => 'Título original']);
    $component = Livewire::actingAs($user)->test(DemandaDetalhe::class, ['demanda' => $segunda])
        ->set('form.titulo', 'Título atualizado')
        ->set('status', 'em_andamento')->call('salvar')->assertHasErrors('status')->assertSee($primeira->titulo);
    expect($segunda->fresh()->status)->toBe(SuporteStatus::Aberta)
        ->and($segunda->fresh()->titulo)->toBe('Título original');
    $primeira->alterarStatus($user, SuporteStatus::EmAnalise);
    $component->set('status', 'em_andamento')->call('salvar')->assertHasNoErrors();
    expect($segunda->fresh()->status)->toBe(SuporteStatus::EmAndamento)
        ->and($segunda->fresh()->titulo)->toBe('Título atualizado');
});

test('banco impede duas demandas em andamento mesmo fora da interface', function () {
    SuporteDemanda::factory()->create(['status' => SuporteStatus::EmAndamento]);
    expect(fn () => SuporteDemanda::factory()->create(['status' => SuporteStatus::EmAndamento]))->toThrow(QueryException::class);
});

test('atividade exige descrição e rejeita conteúdo visualmente vazio', function () {
    $user = usuarioDoSuporte();
    $demanda = SuporteDemanda::factory()->create();
    $component = Livewire::actingAs($user)->test(AtividadeForm::class, ['demanda' => $demanda]);

    $component->call('salvar')->assertHasErrors(['descricao' => 'required']);
    $component->set('descricao', '<p>&nbsp;</p>')->call('salvar')->assertHasErrors('descricao');

    expect($demanda->atividades()->count())->toBe(0);
});

test('publicar atividade ou resposta atrela autor sem duplicar e mantém owner', function () {
    $user = usuarioDoSuporte();
    $outro = usuarioDoSuporte();
    $demanda = SuporteDemanda::factory()->create(['autor_id' => $outro->id]);
    $component = Livewire::actingAs($user)->test(AtividadeForm::class, ['demanda' => $demanda])
        ->set('descricao', '<p>Primeira atividade</p>')->call('salvar')->assertHasNoErrors()->assertSet('descricao', '');
    $atividade = $demanda->atividades()->firstOrFail();
    $component->call('responder', $atividade->uid)->set('descricao', '<p>Resposta</p>')
        ->call('salvar')->assertHasNoErrors();
    expect($demanda->participantes()->where('users.id', $user->id)->count())->toBe(1)
        ->and($atividade->owner_id)->toBe($user->id)
        ->and($demanda->atividades()->whereNotNull('parent_id')->first()->parent_id)->toBe($atividade->id);
});

test('respostas não aceitam filhas nem atividades de outra demanda', function () {
    $user = usuarioDoSuporte();
    $demanda = SuporteDemanda::factory()->create();
    $raiz = SuporteAtividade::factory()->create(['demanda_id' => $demanda->id]);
    $resposta = SuporteAtividade::factory()->create(['demanda_id' => $demanda->id, 'parent_id' => $raiz->id]);
    $outra = SuporteAtividade::factory()->create();

    Livewire::actingAs($user)->test(AtividadeForm::class, ['demanda' => $demanda])
        ->call('responder', $resposta->uid)->assertStatus(404);
    Livewire::actingAs($user)->test(AtividadeForm::class, ['demanda' => $demanda])
        ->call('responder', $outra->uid)->assertStatus(404);
});

test('apenas autor ou admin edita atividade e owner é preservado', function () {
    $autor = usuarioDoSuporte();
    $outro = usuarioDoSuporte();
    $admin = usuarioDoSuporte('admin');
    $atividade = SuporteAtividade::factory()->create(['owner_id' => $autor->id]);
    Livewire::actingAs($outro)->test(AtividadeForm::class, ['demanda' => $atividade->demanda])
        ->call('editar', $atividade->uid)->assertForbidden();
    foreach ([$autor, $admin] as $user) {
        Livewire::actingAs($user)->test(AtividadeForm::class, ['demanda' => $atividade->demanda])
            ->call('editar', $atividade->uid)->set('descricao', '<p>Corrigida</p>')->call('salvar')->assertHasNoErrors();
    }
    expect($atividade->fresh()->owner_id)->toBe($autor->id)
        ->and($atividade->fresh()->descricao)->toContain('Corrigida');
});

test('encerradas bloqueiam alterações mas recebem atividade com ícone e vínculo automático', function (string $status) {
    $user = usuarioDoSuporte();
    $demanda = SuporteDemanda::factory()->create(['status' => $status]);
    Livewire::actingAs($user)->test(DemandaDetalhe::class, ['demanda' => $demanda])
        ->call('salvar')->assertHasErrors('suporte');
    Livewire::test(AtividadeForm::class, ['demanda' => $demanda])
        ->set('descricao', '<p>Retorno após encerramento</p>')->call('salvar')->assertHasNoErrors();
    expect($demanda->fresh()->status->value)->toBe($status)
        ->and($demanda->fresh()->temPendencia())->toBeTrue()
        ->and($demanda->participantes()->where('users.id', $user->id)->exists())->toBeTrue();
    Livewire::test(SuporteTable::class)->set('status', 'todos')->assertSeeHtml('ri-alert-fill text-warning');
    Livewire::test(DemandaDetalhe::class, ['demanda' => $demanda])
        ->set('status', 'em_analise')->call('salvar')->assertHasNoErrors()->assertRedirect(route('suporte-insert', ['demanda' => $demanda->uid]));
})->with(['concluida', 'cancelada']);

test('apenas admin resolve pendência e nova publicação volta a acender aviso', function () {
    $user = usuarioDoSuporte();
    $admin = usuarioDoSuporte('admin');
    $demanda = SuporteDemanda::factory()->concluida()->create(['pendencia_versao' => 1]);
    Livewire::actingAs($user)->test(DemandaDetalhe::class, ['demanda' => $demanda])->call('resolver')->assertForbidden();
    Livewire::actingAs($admin)->test(DemandaDetalhe::class, ['demanda' => $demanda])->call('resolver')->assertHasNoErrors();
    expect($demanda->fresh()->temPendencia())->toBeFalse()
        ->and($demanda->fresh()->status)->toBe(SuporteStatus::Concluida);
    Livewire::actingAs($user)->test(AtividadeForm::class, ['demanda' => $demanda])
        ->set('descricao', '<p>Outra publicação</p>')->call('salvar')->assertHasNoErrors();
    expect($demanda->fresh()->temPendencia())->toBeTrue();
});

test('resolver versão antiga não apaga publicação que admin ainda não viu', function () {
    $admin = usuarioDoSuporte('admin');
    $demanda = SuporteDemanda::factory()->concluida()->create(['pendencia_versao' => 1]);
    $component = Livewire::actingAs($admin)->test(DemandaDetalhe::class, ['demanda' => $demanda]);
    $demanda->update(['pendencia_versao' => 2]);
    $component->call('resolver')->assertHasNoErrors();
    expect($demanda->fresh()->resolvida_versao)->toBe(1)->and($demanda->fresh()->temPendencia())->toBeTrue();
});

test('soft delete exige admin preserva registros e oculta respostas e arquivos', function () {
    $user = usuarioDoSuporte();
    $admin = usuarioDoSuporte('admin');
    $demanda = SuporteDemanda::factory()->create();
    $atividade = SuporteAtividade::factory()->create(['demanda_id' => $demanda->id]);
    $resposta = SuporteAtividade::factory()->create(['demanda_id' => $demanda->id, 'parent_id' => $atividade->id]);
    $arquivo = SuporteArquivo::factory()->create(['demanda_id' => $demanda->id, 'atividade_id' => $resposta->id]);
    Livewire::actingAs($user)->test(DemandaDetalhe::class, ['demanda' => $demanda])->call('excluir')->assertForbidden();
    Livewire::actingAs($admin)->test(DemandaDetalhe::class, ['demanda' => $demanda])
        ->call('excluirAtividade', $atividade->uid)->assertHasNoErrors();
    $this->assertSoftDeleted($atividade);
    $this->assertSoftDeleted($resposta);
    $this->assertSoftDeleted($arquivo);
    Livewire::test(DemandaDetalhe::class, ['demanda' => $demanda])->call('excluir')->assertRedirect(route('suporte-index'));
    $this->assertSoftDeleted($demanda);
    $this->actingAs($admin)->get(route('suporte-insert', $demanda->uid))->assertNotFound();
});

test('usuário sem acesso não pode ordenar nem publicar após perder permissão', function () {
    $user = usuarioDoSuporte();
    $demanda = SuporteDemanda::factory()->create();
    $component = Livewire::actingAs($user)->test(AtividadeForm::class, ['demanda' => $demanda])
        ->set('descricao', '<p>Indevida</p>');
    $user->permissions()->detach();
    $component->call('salvar')->assertNotFound();
    expect($demanda->atividades()->count())->toBe(0);
});

test('admin classifica demanda e registra horas inteiras ao criar e editar', function () {
    $admin = usuarioDoSuporte('admin');
    Livewire::actingAs($admin)->test(DemandaDetalhe::class)
        ->set('form.titulo', 'Classificar demanda')
        ->set('form.descricao', '<p>Descrição da demanda.</p>')
        ->set('form.classificacao', 'corretiva')
        ->set('form.horasGastas', '0')
        ->call('salvar')->assertHasNoErrors();
    $demanda = SuporteDemanda::query()->where('titulo', 'Classificar demanda')->firstOrFail();
    expect($demanda->classificacao)->toBe(SuporteClassificacao::Corretiva)
        ->and($demanda->horas_gastas)->toBe(0);

    Livewire::test(DemandaDetalhe::class, ['demanda' => $demanda])
        ->assertSet('form.classificacao', 'corretiva')
        ->assertSet('form.horasGastas', '0')
        ->set('form.classificacao', 'evolutiva')->set('form.horasGastas', '3')
        ->call('salvar')->assertHasNoErrors();
    expect($demanda->fresh()->classificacao)->toBe(SuporteClassificacao::Evolutiva)
        ->and($demanda->fresh()->horas_gastas)->toBe(3);
});

test('funcionário visualiza classificação e horas sem poder alterá-las mesmo manipulando o formulário', function () {
    $user = usuarioDoSuporte();
    $demanda = SuporteDemanda::factory()->create(['autor_id' => $user->id, 'classificacao' => 'corretiva', 'horas_gastas' => 2]);
    Livewire::actingAs($user)->test(DemandaDetalhe::class, ['demanda' => $demanda])
        ->assertSee('Corretiva')->assertSee('2 h')
        ->assertDontSeeHtml('wire:model="form.classificacao"')
        ->assertDontSeeHtml('wire:model="form.horasGastas"')
        ->set('form.classificacao', 'evolutiva')->set('form.horasGastas', '99')
        ->set('form.titulo', 'Título atualizado')
        ->call('salvar')->assertHasNoErrors();
    expect($demanda->fresh()->classificacao)->toBe(SuporteClassificacao::Corretiva)
        ->and($demanda->fresh()->horas_gastas)->toBe(2)
        ->and($demanda->fresh()->titulo)->toBe('Título atualizado');
});

test('funcionário não define classificação e horas ao cadastrar demanda', function () {
    $user = usuarioDoSuporte();
    Livewire::actingAs($user)->test(DemandaDetalhe::class)
        ->set('form.titulo', 'Cadastro funcionário')->set('form.descricao', '<p>Teste</p>')
        ->set('form.classificacao', 'evolutiva')->set('form.horasGastas', '15')
        ->call('salvar')->assertHasNoErrors();
    $demanda = SuporteDemanda::query()->where('titulo', 'Cadastro funcionário')->firstOrFail();
    expect($demanda->classificacao)->toBeNull()->and($demanda->horas_gastas)->toBe(0);
});

test('valida classificação e horas informadas pelo admin', function (string $campo, string $valor) {
    $admin = usuarioDoSuporte('admin');
    Livewire::actingAs($admin)->test(DemandaDetalhe::class)
        ->set('form.titulo', 'Demanda inválida')->set('form.descricao', '<p>Teste</p>')
        ->set($campo, $valor)->call('salvar')->assertHasErrors($campo);
    expect(SuporteDemanda::count())->toBe(0);
})->with([
    ['form.classificacao', 'outro'],
    ['form.horasGastas', '-1'],
    ['form.horasGastas', '1.123'],
    ['form.horasGastas', '1.50'],
    ['form.horasGastas', '1000000'],
    ['form.horasGastas', 'abc'],
    ['form.horasGastas', ''],
]);

test('admin atualiza dados administrativos de encerrada sem modificar conteúdo nem reabrir', function () {
    $admin = usuarioDoSuporte('admin');
    $demanda = SuporteDemanda::factory()->concluida()->create(['autor_id' => $admin->id]);
    Livewire::actingAs($admin)->test(DemandaDetalhe::class, ['demanda' => $demanda])
        ->assertSeeHtml('wire:model="form.classificacao"')
        ->set('form.classificacao', 'evolutiva')->set('form.horasGastas', '8')
        ->set('form.titulo', 'Conteúdo não permitido')->call('salvar')->assertHasNoErrors();
    expect($demanda->fresh()->status)->toBe(SuporteStatus::Concluida)
        ->and($demanda->fresh()->titulo)->toBe($demanda->titulo)
        ->and($demanda->fresh()->classificacao)->toBe(SuporteClassificacao::Evolutiva)
        ->and($demanda->fresh()->horas_gastas)->toBe(8);
});

test('action rejeita horas fracionadas mesmo sem a validação do formulário', function () {
    $admin = usuarioDoSuporte('admin');
    expect(fn () => app(SalvarDemandaSuporteAction::class)->execute(
        $admin, null,
        ['titulo' => 'Horas inválidas', 'descricao' => '<p>Teste</p>', 'participantes' => [], 'horas_gastas' => '1.5'],
        SuporteArquivo::novoRascunho(null),
    ))->toThrow(ValidationException::class);
    expect(SuporteDemanda::count())->toBe(0);
});
