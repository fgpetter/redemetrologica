<?php

use App\Enums\InterlabLotePostagemStatus;
use App\Models\Permission;
use App\Models\User;
use Database\Factories\InterlabLotePostagemEtiquetaFactory;
use Database\Factories\InterlabLotePostagemFactory;
use Database\Factories\InterlabLotePostagemItemFactory;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

test('funcionario baixa zip com etiquetas e declaracao', function () {
    $user = usuarioFuncionario();
    $lote = loteConcluidoComDocumentos();

    $response = $this->actingAs($user)
        ->get(route('agenda-interlab-lote-postagem-documentos', $lote));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/zip')
        ->and($response->headers->get('content-disposition'))->toContain('lote-'.$lote->uid.'.zip');
});

test('retorna 404 para lote com erro', function () {
    $user = usuarioFuncionario();
    $lote = InterlabLotePostagemFactory::new()->create([
        'status' => InterlabLotePostagemStatus::Erro,
    ]);

    $this->actingAs($user)
        ->get(route('agenda-interlab-lote-postagem-documentos', $lote))
        ->assertNotFound();
});

test('retorna 404 quando falta pdf da declaracao', function () {
    $user = usuarioFuncionario();
    $lote = InterlabLotePostagemFactory::new()->create([
        'status' => InterlabLotePostagemStatus::Concluido,
        'declaracao_conteudo_pdf_path' => 'correios/declaracoes/ausente.pdf',
    ]);

    $etiqueta = InterlabLotePostagemEtiquetaFactory::new()->gerada()->create([
        'interlab_lote_postagem_id' => $lote->id,
        'etiqueta_path' => 'correios/etiquetas/'.$lote->uid.'.pdf',
    ]);
    Storage::disk('local')->put($etiqueta->etiqueta_path, '%PDF-etiqueta');

    InterlabLotePostagemItemFactory::new()->prepostado()->create([
        'interlab_lote_postagem_id' => $lote->id,
        'interlab_lote_postagem_etiqueta_id' => $etiqueta->id,
    ]);

    $this->actingAs($user)
        ->get(route('agenda-interlab-lote-postagem-documentos', $lote))
        ->assertNotFound();
});

test('funcionario baixa zip com etiquetas e dace', function () {
    $user = usuarioFuncionario();
    $lote = InterlabLotePostagemFactory::new()->create([
        'status' => InterlabLotePostagemStatus::Concluido,
        'dace_pdf_path' => 'correios/dace/lote-dace.pdf',
        'declaracao_conteudo_pdf_path' => 'correios/declaracoes/legado.pdf',
    ]);

    $etiqueta = InterlabLotePostagemEtiquetaFactory::new()->gerada()->create([
        'interlab_lote_postagem_id' => $lote->id,
        'etiqueta_path' => 'correios/etiquetas/etiqueta-dace.pdf',
    ]);

    InterlabLotePostagemItemFactory::new()->prepostado()->create([
        'interlab_lote_postagem_id' => $lote->id,
        'interlab_lote_postagem_etiqueta_id' => $etiqueta->id,
    ]);

    Storage::disk('local')->put($etiqueta->etiqueta_path, '%PDF-etiqueta');
    Storage::disk('local')->put($lote->dace_pdf_path, '%PDF-dace');
    Storage::disk('local')->put($lote->declaracao_conteudo_pdf_path, '%PDF-legado');

    $response = $this->actingAs($user)
        ->get(route('agenda-interlab-lote-postagem-documentos', $lote));

    $response->assertOk();

    $zipPath = tempnam(sys_get_temp_dir(), 'assert-zip-');
    file_put_contents($zipPath, $response->streamedContent());

    $zip = new ZipArchive;
    expect($zip->open($zipPath))->toBeTrue()
        ->and($zip->locateName('dace.pdf'))->not->toBeFalse()
        ->and($zip->locateName('declaracao-conteudo.pdf'))->toBeFalse();
    $zip->close();
    unlink($zipPath);
});

function usuarioFuncionario(): User
{
    $user = User::factory()->create();
    $permission = Permission::withoutEvents(fn (): Permission => Permission::query()->firstOrCreate([
        'permission' => 'funcionario',
    ]));
    $user->permissions()->syncWithoutDetaching([$permission->id]);

    return $user;
}

function loteConcluidoComDocumentos(): \App\Models\InterlabLotePostagem
{
    $lote = InterlabLotePostagemFactory::new()->create([
        'status' => InterlabLotePostagemStatus::Concluido,
        'declaracao_conteudo_pdf_path' => 'correios/declaracoes/lote-teste.pdf',
    ]);

    $etiqueta = InterlabLotePostagemEtiquetaFactory::new()->gerada()->create([
        'interlab_lote_postagem_id' => $lote->id,
        'etiqueta_path' => 'correios/etiquetas/etiqueta-teste.pdf',
    ]);

    InterlabLotePostagemItemFactory::new()->prepostado()->create([
        'interlab_lote_postagem_id' => $lote->id,
        'interlab_lote_postagem_etiqueta_id' => $etiqueta->id,
    ]);

    Storage::disk('local')->put($etiqueta->etiqueta_path, '%PDF-etiqueta');
    Storage::disk('local')->put($lote->declaracao_conteudo_pdf_path, '%PDF-declaracao');

    return $lote->fresh(['etiquetas', 'itens']);
}
