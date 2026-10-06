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
        Schema::create('interlab_lote_postagem_itens', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('interlab_lote_postagem_id')->constrained('interlab_lote_postagens')->cascadeOnDelete();
            $table->foreignId('interlab_inscrito_id')->constrained('interlab_inscritos')->restrictOnDelete();
            $table->foreignId('interlab_laboratorio_id')->constrained('interlab_laboratorios')->restrictOnDelete();
            $table->unique(
                ['interlab_lote_postagem_id', 'interlab_laboratorio_id'],
                'ilpi_lote_laboratorio_unique',
            );
            $table->string('codigo_objeto')->nullable()->unique();
            $table->string('id_prepostagem')->nullable();
            $table->unsignedTinyInteger('status_atual')->nullable();
            $table->string('codigo_servico')->nullable();
            $table->string('nu_requisicao')->unique();
            $table->json('consulta_prazo_request')->nullable();
            $table->json('consulta_prazo_response')->nullable();
            $table->timestamp('consulta_prazo_em')->nullable();
            $table->string('motivo_fallback_servico')->nullable();
            $table->json('destinatario');
            $table->string('status')->default('aguardando_classificacao');
            $table->string('status_correios')->nullable();
            $table->string('ultimo_evento')->nullable();
            $table->json('rastreio_payload')->nullable();
            $table->timestamp('rastreado_em')->nullable();
            $table->text('erro_mensagem')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interlab_lote_postagem_itens');
    }
};
