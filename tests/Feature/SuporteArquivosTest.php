<?php

use App\Actions\Suporte\ArquivosSuporteAction;
use App\Actions\Suporte\SalvarDemandaSuporteAction;
use App\Enums\SuporteClassificacao;
use App\Enums\SuporteStatus;
use App\Livewire\Suporte\AtividadeForm;
use App\Livewire\Suporte\DemandaDetalhe;
use App\Mail\SuporteDemandaAtualizadaMail;
use App\Models\Permission;
use App\Models\SuporteArquivo;
use App\Models\SuporteDemanda;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Livewire;

function usuarioArquivosSuporte(string $perfil = 'funcionario'): User
{
    $user = User::factory()->create();
    $permission = Permission::withoutEvents(fn () => Permission::query()->firstOrCreate(['permission' => $perfil]));
    $user->permissions()->syncWithoutDetaching([$permission->id]);

    return $user;
}

test('alterações administrativas não notificam e são persistidas', function (SuporteStatus $inicial, array $administrativo, ?SuporteStatus $status) {
    Mail::fake();
    $user = usuarioArquivosSuporte('admin');
    $this->actingAs($user);
    $demanda = SuporteDemanda::factory()->create(['autor_id' => $user->id, 'status' => $inicial]);
    $dados = ['titulo' => $demanda->titulo, 'descricao' => $demanda->descricao, 'participantes' => [$user->id], ...$administrativo];

    app(SalvarDemandaSuporteAction::class)->execute($user, $demanda, $dados, SuporteArquivo::novoRascunho($demanda), [], $status);

    $demanda->refresh();
    expect($demanda->status)->toBe($status ?? $inicial);
    if (isset($administrativo['horas_gastas'])) {
        expect($demanda->horas_gastas)->toBe($administrativo['horas_gastas']);
    }
    if (isset($administrativo['classificacao'])) {
        expect($demanda->classificacao)->toBe(SuporteClassificacao::from($administrativo['classificacao']));
    }
    Mail::assertNothingQueued();
})->with([
    'sem alterações' => [SuporteStatus::Aberta, [], null],
    'horas aberta' => [SuporteStatus::Aberta, ['horas_gastas' => 5], null],
    'classificação aberta' => [SuporteStatus::Aberta, ['classificacao' => 'corretiva'], null],
    'status aberta' => [SuporteStatus::Aberta, [], SuporteStatus::EmAnalise],
    'combinadas aberta' => [SuporteStatus::Aberta, ['horas_gastas' => 5, 'classificacao' => 'corretiva'], SuporteStatus::Concluida],
    'horas concluída' => [SuporteStatus::Concluida, ['horas_gastas' => 5], null],
    'classificação cancelada' => [SuporteStatus::Cancelada, ['classificacao' => 'corretiva'], null],
    'reabertura' => [SuporteStatus::Concluida, [], SuporteStatus::Aberta],
    'combinadas encerrada' => [SuporteStatus::Cancelada, ['horas_gastas' => 5, 'classificacao' => 'corretiva'], SuporteStatus::Aberta],
]);

test('conteúdo notificável envia email inclusive com alterações administrativas', function (string $campo, bool $combinada) {
    Mail::fake();
    Storage::fake('local');
    $user = usuarioArquivosSuporte('admin');
    $this->actingAs($user);
    $demanda = SuporteDemanda::factory()->create(['autor_id' => $user->id]);
    $token = SuporteArquivo::novoRascunho($demanda);
    $dados = ['titulo' => $demanda->titulo, 'descricao' => $demanda->descricao, 'participantes' => [$user->id]];
    $anexos = [];
    if ($campo === 'titulo') {
        $dados['titulo'] = 'Título alterado';
    } elseif ($campo === 'descricao') {
        $dados['descricao'] = '<p>Descrição alterada</p>';
    } elseif ($campo === 'participantes') {
        $dados['participantes'][] = usuarioArquivosSuporte()->id;
    } else {
        $arquivo = app(ArquivosSuporteAction::class)->enviar($user, UploadedFile::fake()->create('novo.pdf', 1, 'application/pdf'), $token);
        $anexos[] = $arquivo->uid;
    }
    if ($combinada) {
        $dados['horas_gastas'] = 5;
        $dados['classificacao'] = 'evolutiva';
    }

    app(SalvarDemandaSuporteAction::class)->execute($user, $demanda, $dados, $token, $anexos, $combinada ? SuporteStatus::EmAnalise : null);

    Mail::assertQueued(SuporteDemandaAtualizadaMail::class, fn ($mail) => $mail->hasTo($user->email));
    if ($combinada) {
        expect($demanda->fresh()->status)->toBe(SuporteStatus::EmAnalise)
            ->and($demanda->fresh()->horas_gastas)->toBe(5)
            ->and($demanda->fresh()->classificacao)->toBe(SuporteClassificacao::Evolutiva);
    }
})->with(['titulo', 'descricao', 'participantes', 'anexo'])->with([false, true]);

test('demanda encerrada sem alteração permitida continua bloqueada', function () {
    Mail::fake();
    $user = usuarioArquivosSuporte('admin');
    $this->actingAs($user);
    $demanda = SuporteDemanda::factory()->concluida()->create(['autor_id' => $user->id]);
    $dados = ['titulo' => 'Não permitido', 'descricao' => $demanda->descricao, 'participantes' => [$user->id]];

    expect(fn () => app(SalvarDemandaSuporteAction::class)->execute($user, $demanda, $dados, SuporteArquivo::novoRascunho($demanda)))
        ->toThrow(ValidationException::class);
    expect($demanda->fresh()->titulo)->toBe($demanda->titulo);
    Mail::assertNothingQueued();
});

test('upload do editor exige contexto e devolve imagem privada', function () {
    Storage::fake('local');
    $user = usuarioArquivosSuporte();
    $this->actingAs($user);
    $token = SuporteArquivo::novoRascunho(null);
    $response = $this->postJson(route('suporte-imagem-upload', $token), ['upload' => UploadedFile::fake()->image('tela.png')])
        ->assertOk()->assertJsonPath('uploaded', 1);
    $arquivo = SuporteArquivo::query()->firstOrFail();
    Storage::disk('local')->assertExists($arquivo->caminho);
    expect($arquivo->demanda_id)->toBeNull()->and($arquivo->rascunho)->toBe($token);
    $this->get($response->json('url'))->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
    $outro = usuarioArquivosSuporte('admin');
    $this->actingAs($outro)->get($response->json('url'))->assertNotFound();
    $this->postJson(route('suporte-imagem-upload', 'inexistente'), ['upload' => UploadedFile::fake()->image('x.png')])->assertNotFound();
});

test('valida formato e 5 MB nas imagens do editor', function (string $nome, string $mime, int $tamanho) {
    Storage::fake('local');
    $user = usuarioArquivosSuporte();
    $this->actingAs($user);
    $token = SuporteArquivo::novoRascunho(null);
    $this->postJson(route('suporte-imagem-upload', $token), ['upload' => UploadedFile::fake()->create($nome, $tamanho, $mime)])
        ->assertUnprocessable()->assertJsonPath('uploaded', 0);
    expect(SuporteArquivo::count())->toBe(0);
    Storage::disk('local')->assertDirectoryEmpty('suporte/arquivos');
})->with([
    'imagem muito grande' => ['imagem.png', 'image/png', 5121],
    'svg ativo' => ['imagem.svg', 'image/svg+xml', 1],
    'pdf no editor' => ['arquivo.pdf', 'application/pdf', 1],
    'extensão disfarçada' => ['arquivo.php', 'image/png', 1],
]);

test('anexos aceitam formatos previstos e limite exato de 5 MB', function (string $nome, string $mime) {
    Storage::fake('local');
    $user = usuarioArquivosSuporte();
    $this->actingAs($user);
    $token = SuporteArquivo::novoRascunho(null);
    $arquivo = app(ArquivosSuporteAction::class)->enviar($user, UploadedFile::fake()->create($nome, 5120, $mime), $token);
    Storage::disk('local')->assertExists($arquivo->caminho);
    expect($arquivo->tipo)->toBe('anexo')->and($arquivo->tamanho)->toBe(5120 * 1024);
})->with([
    ['foto.jpg', 'image/jpeg'], ['foto.png', 'image/png'], ['foto.gif', 'image/gif'], ['foto.webp', 'image/webp'],
    ['arquivo.pdf', 'application/pdf'], ['arquivo.doc', 'application/msword'],
    ['arquivo.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
    ['arquivo.xls', 'application/vnd.ms-excel'],
    ['arquivo.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
]);

test('mantém anexos de seleções sucessivas sem limite de quantidade', function () {
    Storage::fake('local');
    $user = usuarioArquivosSuporte();
    $component = Livewire::actingAs($user)->test(DemandaDetalhe::class)
        ->set('form.titulo', 'Muitos arquivos')->set('form.descricao', '<p>Arquivos</p>');
    foreach (range(1, 21) as $numero) {
        $component->set('uploads', [UploadedFile::fake()->create("arquivo-{$numero}.pdf", 1, 'application/pdf')])->assertHasNoErrors();
    }
    $component->call('salvar')->assertHasNoErrors();
    $demanda = SuporteDemanda::query()->firstOrFail();
    expect($demanda->arquivos()->count())->toBe(21);
    Storage::disk('local')->assertCount('/suporte/arquivos', 21);
});

test('salva descrição com imagem e bloqueia reutilização de arquivo de outra demanda', function () {
    Storage::fake('local');
    $user = usuarioArquivosSuporte();
    $this->actingAs($user);
    $token = SuporteArquivo::novoRascunho(null);
    $imagem = app(ArquivosSuporteAction::class)->enviar($user, UploadedFile::fake()->image('captura.png'), $token, true);
    $html = '<figure class="image"><img src="'.route('suporte-arquivo-exibir', $imagem->uid).'"><figcaption>Evidência</figcaption></figure>';
    $demanda = app(SalvarDemandaSuporteAction::class)->execute($user, null, [
        'titulo' => 'Demanda com imagem', 'descricao' => $html, 'participantes' => [],
    ], $token);
    expect($imagem->fresh()->rascunho)->toBeNull()->and($imagem->fresh()->demanda_id)->toBe($demanda->id)
        ->and($demanda->descricao)->toContain('<figure')->toContain('Evidência')->not->toContain('<script');
    $outroToken = SuporteArquivo::novoRascunho(null);
    expect(fn () => app(SalvarDemandaSuporteAction::class)->execute($user, null, [
        'titulo' => 'Outra demanda', 'descricao' => $html, 'participantes' => [],
    ], $outroToken))->toThrow(ValidationException::class);
    expect(SuporteDemanda::count())->toBe(1);
    Storage::disk('local')->assertExists($imagem->caminho);
});

test('atividade nova não remove nem captura anexos da demanda', function () {
    Storage::fake('local');
    $user = usuarioArquivosSuporte();
    $demanda = SuporteDemanda::factory()->create();
    $arquivo = SuporteArquivo::factory()->create(['demanda_id' => $demanda->id, 'user_id' => $user->id]);
    Storage::disk('local')->put($arquivo->caminho, 'arquivo');
    Livewire::actingAs($user)->test(AtividadeForm::class, ['demanda' => $demanda])
        ->set('descricao', '<p>Atividade</p>')->call('salvar')->assertHasNoErrors();
    expect($arquivo->fresh()->atividade_id)->toBeNull()->and($arquivo->fresh()->deleted_at)->toBeNull();
    Storage::disk('local')->assertExists($arquivo->caminho);
});

test('anexo forjado não vincula arquivo alheio e desfaz publicação', function () {
    Storage::fake('local');
    $user = usuarioArquivosSuporte();
    $demanda = SuporteDemanda::factory()->create();
    $outro = SuporteArquivo::factory()->create();
    Livewire::actingAs($user)->test(AtividadeForm::class, ['demanda' => $demanda])
        ->set('descricao', '<p>Tentativa</p>')->set('anexos', [$outro->uid])
        ->call('salvar')->assertHasErrors('suporte');
    expect($demanda->atividades()->count())->toBe(0)
        ->and($outro->fresh()->demanda_id)->not->toBe($demanda->id);
    Storage::disk('local')->assertDirectoryEmpty('/suporte/arquivos');
});

test('remover anexo exige edição permitida e bloqueia download preservando arquivo', function () {
    Storage::fake('local');
    $user = usuarioArquivosSuporte();
    $demanda = SuporteDemanda::factory()->create(['autor_id' => $user->id]);
    $arquivo = SuporteArquivo::factory()->create(['demanda_id' => $demanda->id]);
    $demanda->participantes()->sync([$user->id]);
    Mail::fake();
    Storage::disk('local')->put($arquivo->caminho, 'conteudo');
    $this->actingAs($user)->get(route('suporte-arquivo-download', $arquivo->uid))->assertOk()->assertDownload('exemplo.pdf');
    Livewire::actingAs($user)->test(DemandaDetalhe::class, ['demanda' => $demanda])
        ->call('removerAnexo', $arquivo->uid)->call('salvar')->assertHasNoErrors();
    $this->assertSoftDeleted($arquivo);
    Mail::assertQueued(SuporteDemandaAtualizadaMail::class, fn ($mail) => $mail->hasTo($user->email));
    $this->get(route('suporte-arquivo-download', $arquivo->uid))->assertNotFound();
    Storage::disk('local')->assertExists($arquivo->caminho);
});

test('arquivos publicados exigem perfil interno e pais ativos', function () {
    Storage::fake('local');
    $arquivo = SuporteArquivo::factory()->create();
    Storage::disk('local')->put($arquivo->caminho, 'conteudo');
    $this->get(route('suporte-arquivo-download', $arquivo->uid))->assertRedirect(route('login'));
    $this->actingAs(usuarioArquivosSuporte('cliente'))->get(route('suporte-arquivo-download', $arquivo->uid))->assertNotFound();
    $this->actingAs(usuarioArquivosSuporte('admin'))->get(route('suporte-arquivo-download', $arquivo->uid))->assertOk();
    $arquivo->demanda->delete();
    $this->get(route('suporte-arquivo-download', $arquivo->uid))->assertNotFound();
    Storage::disk('local')->assertExists($arquivo->caminho);
});

test('limpa apenas temporários antigos preservando recentes e arquivos publicados', function () {
    Storage::fake('local');
    Storage::fake('ckeditor-public');
    $this->freezeTime();
    $antigo = SuporteArquivo::factory()->create(['demanda_id' => null, 'rascunho' => (string) Str::uuid(), 'created_at' => now()->subHours(25)]);
    $recente = SuporteArquivo::factory()->create(['demanda_id' => null, 'rascunho' => (string) Str::uuid()]);
    $publicado = SuporteArquivo::factory()->create(['created_at' => now()->subDays(2)]);
    foreach ([$antigo, $recente, $publicado] as $arquivo) {
        Storage::disk('local')->put($arquivo->caminho, 'conteudo');
    }
    $publicPathOriginal = public_path();
    try {
        app()->usePublicPath(Storage::disk('ckeditor-public')->path(''));
        $this->artisan('suporte:limpar-uploads')->assertSuccessful();
    } finally {
        app()->usePublicPath($publicPathOriginal);
    }
    Storage::disk('local')->assertMissing($antigo->caminho);
    Storage::disk('local')->assertExists([$recente->caminho, $publicado->caminho]);
    $this->assertDatabaseMissing('suporte_arquivos', ['id' => $antigo->id]);
});

test('preserva metadados e remove upload temporário real após persistir', function () {
    Storage::fake('local');
    $user = usuarioArquivosSuporte();
    $this->actingAs($user);
    $token = SuporteArquivo::novoRascunho(null);
    $nome = TemporaryUploadedFile::generateHashNameWithOriginalNameEmbedded(UploadedFile::fake()->create('real.pdf', 1, 'application/pdf'));
    $conteudo = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF";
    Storage::disk('local')->put('livewire-tmp/'.$nome, $conteudo);
    $upload = new TemporaryUploadedFile($nome, 'local');
    $arquivo = app(ArquivosSuporteAction::class)->enviar($user, $upload, $token);
    expect($arquivo->nome)->toBe('real.pdf')->and($arquivo->mime)->toBe('application/pdf')
        ->and($arquivo->tamanho)->toBe(strlen($conteudo));
    Storage::disk('local')->assertExists($arquivo->caminho);
    Storage::disk('local')->assertMissing('livewire-tmp/'.$nome);
});

test('falha de persistência remove arquivo gravado e preserva upload temporário para nova tentativa', function () {
    Storage::fake('local');
    $user = usuarioArquivosSuporte();
    $this->actingAs($user);
    $token = SuporteArquivo::novoRascunho(null);
    $nome = TemporaryUploadedFile::generateHashNameWithOriginalNameEmbedded(UploadedFile::fake()->create('erro.pdf', 1, 'application/pdf'));
    Storage::disk('local')->put('livewire-tmp/'.$nome, "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF");
    $upload = new TemporaryUploadedFile($nome, 'local');
    SuporteArquivo::creating(function (): void {
        throw new RuntimeException('Falha simulada');
    });
    expect(fn () => app(ArquivosSuporteAction::class)->enviar($user, $upload, $token))
        ->toThrow(RuntimeException::class, 'Falha simulada');
    Storage::disk('local')->assertDirectoryEmpty('/suporte/arquivos');
    Storage::disk('local')->assertExists('livewire-tmp/'.$nome);
    expect(SuporteArquivo::count())->toBe(0);
});
