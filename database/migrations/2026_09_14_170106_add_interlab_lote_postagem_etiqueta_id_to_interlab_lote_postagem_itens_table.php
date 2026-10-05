<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('interlab_lote_postagem_itens', function (Blueprint $table) {
            $table->foreignId('interlab_lote_postagem_etiqueta_id')
                ->nullable()
                ->after('status')
                ->constrained(
                    table: 'interlab_lote_postagem_etiquetas',
                    indexName: 'ilpi_etiqueta_id_foreign',
                )
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interlab_lote_postagem_itens', function (Blueprint $table) {
            $table->dropForeign('ilpi_etiqueta_id_foreign');
            $table->dropColumn('interlab_lote_postagem_etiqueta_id');
        });
    }
};
