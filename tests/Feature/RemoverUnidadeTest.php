<?php

use Illuminate\Support\Facades\Schema;

test('schema nao possui tabela unidades nem coluna unidade_id', function () {
    expect(Schema::hasTable('unidades'))->toBeFalse()
        ->and(Schema::hasColumn('enderecos', 'unidade_id'))->toBeFalse()
        ->and(Schema::hasColumn('notas_fiscais', 'unidade_id'))->toBeFalse();
});

test('rotas de unidade nao existem', function () {
    expect(collect(['unidade-create', 'unidade-update', 'unidade-delete'])
        ->every(fn (string $name) => ! app('router')->has($name)))->toBeTrue();
});

test('modelo Unidade foi removido', function () {
    expect(file_exists(app_path('Models/Unidade.php')))->toBeFalse();
});
