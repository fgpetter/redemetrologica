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
        Schema::create('interlab_inscritos', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('pessoa_id')->constrained();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('laboratorio_id')->nullable();
            $table->foreignId('agenda_interlab_id')->constrained()->onDelete('cascade');
            $table->dateTime('data_inscricao');
            $table->decimal('valor', 8, 2)->nullable();
            $table->foreignId('pesquisa_id')->nullable();
            $table->dateTime('resposta_pesquisa')->nullable();
            $table->dateTime('certificado_emitido')->nullable();
            $table->string('certificado_path')->nullable();
            $table->text('informacoes_inscricao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interlab_inscritos');
    }
};
