<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->publicPathOriginal = public_path();
});

afterEach(function () {
    app()->usePublicPath($this->publicPathOriginal);
});

test('limpa pastas antigas do CKEditor preservando recentes publicadas e diretórios de outros formatos', function () {
    Storage::fake('ckeditor-public');
    app()->usePublicPath(Storage::disk('ckeditor-public')->path(''));
    $this->freezeTime();
    $antigo = now()->subHours(25)->getTimestamp();
    $recente = now()->subHour()->getTimestamp();

    $pastas = [
        'Temp123456789' => [$antigo, $antigo],
        'Temp234567890' => [$recente, $recente],
        'Temp345678901' => [$antigo, $recente],
        'interlab-media' => [$antigo, $antigo],
        'Temp-outro' => [$antigo, $antigo],
    ];
    foreach ($pastas as $pasta => [$modificacaoPasta, $modificacaoArquivo]) {
        File::makeDirectory(public_path($pasta));
        File::put(public_path($pasta.'/imagem.jpg'), 'imagem');
        touch(public_path($pasta.'/imagem.jpg'), $modificacaoArquivo);
        touch(public_path($pasta), $modificacaoPasta);
    }
    File::makeDirectory(public_path('Temp456789012'));
    touch(public_path('Temp456789012'), $antigo);
    symlink(public_path('interlab-media'), public_path('Temp567890123'));

    $this->artisan('suporte:limpar-uploads')
        ->expectsOutput('Pastas temporárias do CKEditor removidas: 2.')
        ->assertSuccessful();

    expect(File::exists(public_path('Temp123456789')))->toBeFalse();
    expect(File::exists(public_path('Temp456789012')))->toBeFalse();
    foreach (['Temp234567890', 'Temp345678901', 'interlab-media', 'Temp-outro'] as $pasta) {
        expect(File::exists(public_path($pasta.'/imagem.jpg')))->toBeTrue();
    }
    expect(is_link(public_path('Temp567890123')))->toBeTrue();
});

test('preserva pastas e imagens no limite exato de 24 horas', function () {
    Storage::fake('ckeditor-public');
    app()->usePublicPath(Storage::disk('ckeditor-public')->path(''));
    $this->freezeTime();
    $limite = now()->subDay()->getTimestamp();
    File::makeDirectory(public_path('Temp123456789'));
    File::put(public_path('Temp123456789/imagem.jpg'), 'imagem');
    touch(public_path('Temp123456789/imagem.jpg'), $limite);
    touch(public_path('Temp123456789'), $limite - 1);
    File::makeDirectory(public_path('Temp234567890'));
    touch(public_path('Temp234567890'), $limite);

    $this->artisan('suporte:limpar-uploads')->assertSuccessful();

    expect(File::exists(public_path('Temp123456789/imagem.jpg')))->toBeTrue();
    expect(File::isDirectory(public_path('Temp234567890')))->toBeTrue();
});
