<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Garante o unique composto em bancos que já rodaram a migration de criação
     * sem o índice, e remove uniques isolados de uma versão anterior da mesma tabela.
     */
    public function up(): void
    {
        foreach ([
            'interlab_lote_postagem_itens_interlab_lote_postagem_id_unique',
            'interlab_lote_postagem_itens_interlab_laboratorio_id_unique',
        ] as $nome) {
            if ($this->temIndice($nome)) {
                Schema::table('interlab_lote_postagem_itens', function (Blueprint $table) use ($nome): void {
                    $table->dropUnique($nome);
                });
            }
        }

        if (! $this->temIndice('ilpi_lote_laboratorio_unique')) {
            Schema::table('interlab_lote_postagem_itens', function (Blueprint $table): void {
                $table->unique(
                    ['interlab_lote_postagem_id', 'interlab_laboratorio_id'],
                    'ilpi_lote_laboratorio_unique',
                );
            });
        }
    }

    public function down(): void
    {
        if ($this->temIndice('ilpi_lote_laboratorio_unique')) {
            Schema::table('interlab_lote_postagem_itens', function (Blueprint $table): void {
                $table->dropUnique('ilpi_lote_laboratorio_unique');
            });
        }
    }

    private function temIndice(string $nome): bool
    {
        return collect(Schema::getIndexes('interlab_lote_postagem_itens'))
            ->contains(fn (array $index): bool => $index['name'] === $nome);
    }
};
