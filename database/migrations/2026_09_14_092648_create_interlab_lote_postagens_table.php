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
        Schema::create('interlab_lote_postagens', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('agenda_interlab_id')->constrained('agenda_interlabs')->cascadeOnDelete();
            $table->string('nome');
            $table->string('codigo_formato')->default('2');
            $table->string('altura', 3)->nullable();
            $table->string('largura', 3)->nullable();
            $table->string('comprimento', 3)->nullable();
            $table->string('diametro', 3)->nullable();
            $table->unsignedInteger('peso_gramas');
            $table->json('declaracao_conteudo');
            $table->string('status');
            $table->timestamp('ultima_progressao_em')->nullable();
            $table->string('declaracao_conteudo_path')->nullable();
            $table->string('declaracao_conteudo_pdf_path')->nullable();
            $table->string('etapa_erro')->nullable();
            $table->text('erro_mensagem')->nullable();
            $table->timestamps();

            $table->index(['agenda_interlab_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interlab_lote_postagens');
    }
};
