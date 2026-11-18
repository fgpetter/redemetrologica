<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    $this->migration = require database_path('migrations/2026_11_11_164739_drop_legacy_curso_materiais_tables.php');
    $this->migration->down();
    $this->originalPublic = app()->publicPath();
    $this->public = sys_get_temp_dir().'/materiais-legados-'.bin2hex(random_bytes(8));
    mkdir($this->public.'/curso-material', 0700, true);
    app()->usePublicPath($this->public);
});

afterEach(function () {
    app()->usePublicPath($this->originalPublic);
    File::deleteDirectory($this->public);
});

function materialLegado(string $arquivo, int $id): void
{
    DB::table('curso_materiais')->insert(['id' => $id, 'uid' => 'uid-'.$id, 'curso_id' => 1, 'arquivo' => $arquivo]);
    DB::table('agenda_curso_materiais')->insert(['agenda_curso_id' => 1, 'curso_material_id' => $id]);
}

it('remove arquivos existentes e ausentes com referências repetidas sem tocar arquivos não relacionados', function () {
    file_put_contents($this->public.'/curso-material/existe.pdf', 'x');
    file_put_contents($this->public.'/curso-material/outro.pdf', 'x');
    materialLegado('existe.pdf', 1);
    materialLegado('existe.pdf', 2);
    materialLegado('ausente.pdf', 3);

    $this->artisan('cursos:limpar-materiais-legados')->assertExitCode(0);
    expect(file_exists($this->public.'/curso-material/existe.pdf'))->toBeFalse()
        ->and(file_exists($this->public.'/curso-material/outro.pdf'))->toBeTrue()
        ->and(DB::table('curso_materiais')->count())->toBe(0)
        ->and(DB::table('agenda_curso_materiais')->count())->toBe(0);
    $this->migration->up();
    $this->artisan('cursos:limpar-materiais-legados')->assertExitCode(0);
});

it('preserva referências inseguras e não segue links simbólicos', function () {
    file_put_contents($this->public.'/fora.pdf', 'protegido');
    symlink($this->public.'/fora.pdf', $this->public.'/curso-material/link.pdf');
    materialLegado('../fora.pdf', 1);
    materialLegado('link.pdf', 2);
    $this->artisan('cursos:limpar-materiais-legados')->assertExitCode(1);
    expect(file_get_contents($this->public.'/fora.pdf'))->toBe('protegido')
        ->and(DB::table('curso_materiais')->count())->toBe(2)
        ->and(DB::table('agenda_curso_materiais')->count())->toBe(2);
});

it('preserva referências quando a exclusão falha e permite nova tentativa', function () {
    mkdir($this->public.'/curso-material/diretorio.pdf');
    materialLegado('diretorio.pdf', 1);
    $this->artisan('cursos:limpar-materiais-legados')->assertExitCode(1);
    expect(DB::table('curso_materiais')->count())->toBe(1)
        ->and(DB::table('agenda_curso_materiais')->count())->toBe(1);
    rmdir($this->public.'/curso-material/diretorio.pdf');
    $this->artisan('cursos:limpar-materiais-legados')->assertExitCode(0);
});

it('recusa diretório legado que seja link simbólico', function () {
    rmdir($this->public.'/curso-material');
    mkdir($this->public.'/fora');
    file_put_contents($this->public.'/fora/arquivo.pdf', 'protegido');
    symlink($this->public.'/fora', $this->public.'/curso-material');
    materialLegado('arquivo.pdf', 1);
    $this->artisan('cursos:limpar-materiais-legados')->assertExitCode(1);
    expect(file_exists($this->public.'/fora/arquivo.pdf'))->toBeTrue()
        ->and(DB::table('curso_materiais')->count())->toBe(1);
});

it('preserva referências quando o diretório de materiais não permite travessia', function () {
    file_put_contents($this->public.'/curso-material/arquivo.pdf', 'protegido');
    materialLegado('arquivo.pdf', 1);
    chmod($this->public.'/curso-material', 0444);
    clearstatcache();
    try {
        $this->artisan('cursos:limpar-materiais-legados')->assertExitCode(1);
        expect(DB::table('curso_materiais')->count())->toBe(1)
            ->and(DB::table('agenda_curso_materiais')->count())->toBe(1);
    } finally {
        chmod($this->public.'/curso-material', 0700);
    }
    expect(file_get_contents($this->public.'/curso-material/arquivo.pdf'))->toBe('protegido');
});

it('bloqueia a migration enquanto qualquer tabela tem dados', function () {
    materialLegado('arquivo.pdf', 1);
    expect(fn () => $this->migration->up())->toThrow(RuntimeException::class);
    expect(Schema::hasTable('curso_materiais'))->toBeTrue();
    DB::table('agenda_curso_materiais')->delete();
    expect(fn () => $this->migration->up())->toThrow(RuntimeException::class);
    DB::table('curso_materiais')->delete();
    $this->migration->up();
    expect(Schema::hasTable('curso_materiais'))->toBeFalse()
        ->and(Schema::hasTable('agenda_curso_materiais'))->toBeFalse();
});
