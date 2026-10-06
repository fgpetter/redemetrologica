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
        Schema::create('interlab_lote_postagem_etiquetas', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('interlab_lote_postagem_id')->constrained(
                table: 'interlab_lote_postagens',
                indexName: 'ilpe_lote_postagem_id_foreign',
            )->cascadeOnDelete();
            $table->timestamp('solicitacao_iniciada_em')->nullable();
            $table->string('id_recibo_rotulo')->nullable();
            $table->string('etiqueta_path')->nullable();
            $table->string('status')->default('pendente');
            $table->string('etapa_erro')->nullable();
            $table->text('erro_mensagem')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interlab_lote_postagem_etiquetas');
    }
};
