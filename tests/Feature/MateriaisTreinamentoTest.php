<?php

use App\Models\AgendaCursoArquivo;
use App\Models\AgendaCursos;
use App\Models\Curso;
use App\Models\Instrutor;
use App\Models\User;
use Database\Factories\PessoaFactory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

beforeEach(function () {
    expect(DB::connection()->getDatabaseName())->toBe(':memory:');
    Storage::fake('public');
    $curso = Curso::create(['descricao' => 'Curso', 'tipo_curso' => 'OFICIAL']);
    $instrutor = Instrutor::create(['pessoa_id' => PessoaFactory::new()->create()->id, 'situacao' => true]);
    $this->agenda = AgendaCursos::create(['curso_id' => $curso->id, 'instrutor_id' => $instrutor->id, 'status' => 'AGENDADO', 'tipo_agendamento' => 'ONLINE']);
    $this->user = User::factory()->create();
    $permission = DB::table('permissions')->insertGetId(['permission' => 'funcionario', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('permission_user')->insert(['permission_id' => $permission, 'user_id' => $this->user->id]);
});

it('uploads and deletes a material in the public disk', function () {
    $this->actingAs($this->user)->post(route('agendamento-curso-upload-material', $this->agenda->uid), [
        'arquivo' => UploadedFile::fake()->createWithContent('material.pdf', '%PDF-1.4 test'), 'descricao' => 'Apostila',
    ])->assertSessionHas('success');
    $material = $this->agenda->materiaisTreinamento()->firstOrFail();
    expect($material->uid)->not->toBeEmpty()->and($material->descricao)->toBe('Apostila')
        ->and($material->arquivo)->toStartWith('agendamento-curso-material/'.$this->agenda->uid.'/');
    Storage::disk('public')->assertExists($material->arquivo);
    $this->delete(route('agendamento-curso-delete-material', [$this->agenda->uid, $material->uid]))->assertSessionHas('success');
    Storage::disk('public')->assertMissing($material->arquivo);
    $this->assertDatabaseMissing('agenda_curso_arquivos', ['id' => $material->id]);
});

it('names uploads using the original filename and preserves collisions', function () {
    $this->freezeTime();
    $url = route('agendamento-curso-upload-material', $this->agenda->uid);
    $this->actingAs($this->user);

    foreach (['%PDF-1.4 first', '%PDF-1.4 second'] as $content) {
        $this->post($url, [
            'arquivo' => UploadedFile::fake()->createWithContent('Apostila de Formação.PDF', $content),
        ])->assertSessionHas('success');
    }

    $directory = 'agendamento-curso-material/'.$this->agenda->uid;
    $base = $directory.'/apostila-de-formacao_'.now()->timestamp;
    $paths = $this->agenda->materiaisTreinamento()->orderBy('id')->pluck('arquivo')->all();
    expect($paths)->toBe([$base.'.pdf', $base.'_1.pdf']);
    expect(Storage::disk('public')->get($paths[0]))->toBe('%PDF-1.4 first')
        ->and(Storage::disk('public')->get($paths[1]))->toBe('%PDF-1.4 second');
});

it('uses a safe filename when the original name has no slug', function () {
    $this->freezeTime();
    $this->actingAs($this->user)->post(route('agendamento-curso-upload-material', $this->agenda->uid), [
        'arquivo' => UploadedFile::fake()->createWithContent('!!!.pdf', '%PDF-1.4 test'),
    ])->assertSessionHas('success');

    $path = 'agendamento-curso-material/'.$this->agenda->uid.'/material_'.now()->timestamp.'.pdf';
    expect($this->agenda->materiaisTreinamento()->firstOrFail()->arquivo)->toBe($path);
    Storage::disk('public')->assertExists($path);
});

it('rejects missing files and mismatched extensions', function () {
    $url = route('agendamento-curso-upload-material', $this->agenda->uid);
    $this->actingAs($this->user)->post($url, [])->assertSessionHasErrors('arquivo');
    $file = tmpfile();
    fwrite($file, '%PDF-1.4 test');
    $upload = new UploadedFile(stream_get_meta_data($file)['uri'], 'documento.png', null, null, true);
    $this->post($url, ['arquivo' => $upload])->assertSessionHasErrors('arquivo');
    fclose($file);
    $this->post($url, ['arquivo' => UploadedFile::fake()->create('teste.pdf', 5121, 'application/pdf')])->assertSessionHasErrors('arquivo');
    $this->post($url, ['arquivo' => UploadedFile::fake()->createWithContent('teste.pdf', '%PDF-1.4 test'), 'descricao' => str_repeat('a', 191)])->assertSessionHasErrors('descricao');
});

it('blocks access to another schedule material and cleans up on schedule deletion', function () {
    $other = $this->agenda->replicate();
    $other->uid = null;
    $other->save();
    Storage::disk('public')->put('material.pdf', 'test');
    $material = $this->agenda->materiaisTreinamento()->create(['arquivo' => 'material.pdf']);
    $this->actingAs($this->user)->delete(route('agendamento-curso-delete-material', [$other->uid, $material->uid]))->assertNotFound();
    Storage::disk('public')->assertExists('material.pdf');
    $this->post(route('agendamento-curso-delete', $this->agenda->uid))->assertSessionHas('warning');
    Storage::disk('public')->assertMissing('material.pdf');
    $this->assertDatabaseMissing('agenda_curso_arquivos', ['id' => $material->id]);
});

it('requires authentication and permission', function () {
    $url = route('agendamento-curso-upload-material', $this->agenda->uid);
    $material = $this->agenda->materiaisTreinamento()->create(['arquivo' => 'protegido.pdf']);
    $deleteUrl = route('agendamento-curso-delete-material', [$this->agenda->uid, $material->uid]);
    $this->post($url)->assertRedirect(route('login'));
    $this->delete($deleteUrl)->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create())->post($url)->assertNotFound();
    $this->delete($deleteUrl)->assertNotFound();
    $this->assertDatabaseHas('agenda_curso_arquivos', ['id' => $material->id]);
});

it('accepts real image and docx content and returns to the editing page', function (string $extension) {
    $file = tmpfile();
    $path = stream_get_meta_data($file)['uri'];

    try {
        if ($extension === 'docx') {
            $document = new PhpWord;
            $document->addSection()->addText('Material de treinamento');
            IOFactory::createWriter($document, 'Word2007')->save($path);
        } else {
            $image = imagecreatetruecolor(10, 10);
            if ($extension === 'png') {
                imagepng($image, $path);
            } else {
                imagejpeg($image, $path);
            }
            imagedestroy($image);
        }

        $editUrl = route('agendamento-curso-insert', $this->agenda->uid);
        $this->actingAs($this->user)->from($editUrl)->post(route('agendamento-curso-upload-material', $this->agenda->uid), [
            'arquivo' => new UploadedFile($path, 'material.'.$extension, null, null, true),
        ])->assertRedirect($editUrl)->assertSessionHas('success');

        $material = $this->agenda->materiaisTreinamento()->firstOrFail();
        Storage::disk('public')->assertExists($material->arquivo);
        $this->from($editUrl)->delete(route('agendamento-curso-delete-material', [$this->agenda->uid, $material->uid]))
            ->assertRedirect($editUrl)->assertSessionHas('success');
    } finally {
        fclose($file);
    }
})->with(['jpg', 'jpeg', 'png', 'docx']);

it('rejects invalid file content and records validation context', function () {
    $file = tmpfile();
    $log = tmpfile();
    $logPath = stream_get_meta_data($log)['uri'];
    config(['logging.channels.validation' => ['driver' => 'single', 'path' => $logPath, 'level' => 'info']]);
    Log::forgetChannel('validation');

    try {
        fwrite($file, 'Conteúdo de texto inválido');
        $editUrl = route('agendamento-curso-insert', $this->agenda->uid);
        $this->actingAs($this->user)->from($editUrl)->post(route('agendamento-curso-upload-material', $this->agenda->uid), [
            'arquivo' => new UploadedFile(stream_get_meta_data($file)['uri'], 'invalido.txt', null, null, true),
        ])->assertRedirect($editUrl)->assertSessionHasErrors('arquivo')->assertSessionHas('error');

        $contents = file_get_contents($logPath);
        expect($contents)->toContain('Erro de validação de material de treinamento')
            ->toContain('invalido.txt')->toContain($this->agenda->uid)
            ->toContain('"user_id":'.$this->user->id)->toContain('arquivo');
        expect($this->agenda->materiaisTreinamento()->count())->toBe(0);
    } finally {
        Log::forgetChannel('validation');
        fclose($file);
        fclose($log);
    }
});

it('logs and rejects an array of uploaded files without a server error', function () {
    $log = tmpfile();
    $logPath = stream_get_meta_data($log)['uri'];
    config(['logging.channels.validation' => ['driver' => 'single', 'path' => $logPath, 'level' => 'info']]);
    Log::forgetChannel('validation');

    try {
        $editUrl = route('agendamento-curso-insert', $this->agenda->uid);
        $this->actingAs($this->user)->from($editUrl)->post(route('agendamento-curso-upload-material', $this->agenda->uid), [
            'arquivo' => [UploadedFile::fake()->createWithContent('material.pdf', '%PDF-1.4 test')],
        ])->assertRedirect($editUrl)->assertSessionHasErrors('arquivo')->assertSessionHas('error');

        expect(file_get_contents($logPath))->toContain('Erro de validação de material de treinamento')
            ->toContain($this->agenda->uid)->toContain('"arquivo":null');
        expect($this->agenda->materiaisTreinamento()->count())->toBe(0);
    } finally {
        Log::forgetChannel('validation');
        fclose($log);
    }
});

it('preserves the schedule and material when physical deletion fails', function () {
    $material = $this->agenda->materiaisTreinamento()->create(['arquivo' => 'falha-turma.pdf']);
    $disk = Mockery::mock(Filesystem::class);
    $disk->shouldReceive('exists')->once()->with('falha-turma.pdf')->andReturnTrue();
    $disk->shouldReceive('delete')->once()->with('falha-turma.pdf')->andReturnFalse();
    Storage::shouldReceive('disk')->once()->with('public')->andReturn($disk);

    $editUrl = route('agendamento-curso-insert', $this->agenda->uid);
    $this->actingAs($this->user)->from($editUrl)->post(route('agendamento-curso-delete', $this->agenda->uid))
        ->assertRedirect($editUrl)->assertSessionHas('error');
    $this->assertDatabaseHas('agenda_cursos', ['id' => $this->agenda->id]);
    $this->assertDatabaseHas('agenda_curso_arquivos', ['id' => $material->id, 'arquivo' => 'falha-turma.pdf']);
});

it('tolerates a missing file but preserves references when deletion fails', function () {
    $missing = $this->agenda->materiaisTreinamento()->create(['arquivo' => 'ausente.pdf']);
    $this->actingAs($this->user)->delete(route('agendamento-curso-delete-material', [$this->agenda->uid, $missing->uid]))->assertSessionHas('success');
    $this->assertDatabaseMissing('agenda_curso_arquivos', ['id' => $missing->id]);

    $material = $this->agenda->materiaisTreinamento()->create(['arquivo' => 'falha.pdf']);
    $disk = Mockery::mock(Filesystem::class);
    $disk->shouldReceive('exists')->once()->with('falha.pdf')->andReturnTrue();
    $disk->shouldReceive('delete')->once()->with('falha.pdf')->andReturnFalse();
    Storage::shouldReceive('disk')->once()->with('public')->andReturn($disk);
    $this->delete(route('agendamento-curso-delete-material', [$this->agenda->uid, $material->uid]))->assertSessionHas('error');
    $this->assertDatabaseHas('agenda_curso_arquivos', ['id' => $material->id]);
});

it('removes an uploaded file if the record cannot be saved', function () {
    AgendaCursoArquivo::creating(fn () => throw new RuntimeException('database failure'));
    try {
        $this->actingAs($this->user)->post(route('agendamento-curso-upload-material', $this->agenda->uid), [
            'arquivo' => UploadedFile::fake()->createWithContent('material.pdf', '%PDF-1.4 test'),
        ])->assertSessionHas('error');
        Storage::disk('public')->assertDirectoryEmpty('agendamento-curso-material/'.$this->agenda->uid);
        expect($this->agenda->materiaisTreinamento()->count())->toBe(0);
    } finally {
        AgendaCursoArquivo::flushEventListeners();
    }
});

it('rolls back the record and removes the file if a created listener fails', function () {
    AgendaCursoArquivo::created(fn () => throw new RuntimeException('post-insert failure'));
    try {
        $this->actingAs($this->user)->post(route('agendamento-curso-upload-material', $this->agenda->uid), [
            'arquivo' => UploadedFile::fake()->createWithContent('material.pdf', '%PDF-1.4 test'),
        ])->assertSessionHas('error');

        Storage::disk('public')->assertDirectoryEmpty('agendamento-curso-material/'.$this->agenda->uid);
        expect($this->agenda->materiaisTreinamento()->count())->toBe(0);
    } finally {
        AgendaCursoArquivo::flushEventListeners();
    }
});
