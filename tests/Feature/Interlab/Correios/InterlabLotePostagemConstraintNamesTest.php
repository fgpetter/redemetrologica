<?php

use Illuminate\Support\Facades\Schema;

test('usa nomes de foreign key e indice com no maximo 64 caracteres nas tabelas de lote de postagem', function () {
    foreach ([
        'interlab_lote_postagem_etiquetas',
        'interlab_lote_postagem_itens',
    ] as $tabela) {
        foreach (Schema::getForeignKeys($tabela) as $foreignKey) {
            expect(strlen($foreignKey['name']))->toBeLessThanOrEqual(64);
        }

        foreach (Schema::getIndexes($tabela) as $index) {
            expect(strlen($index['name']))->toBeLessThanOrEqual(64);
        }
    }
});
