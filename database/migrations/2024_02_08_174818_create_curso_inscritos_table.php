<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('curso_inscritos', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->default(new Expression("(replace(left(uuid(),12),_utf8mb3'-',_utf8mb4'0'))"))->unique();
            $table->foreignId('pessoa_id')->constrained();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->foreignId('agenda_curso_id')->constrained();
            $table->decimal('valor')->nullable();
            $table->dateTime('data_confirmacao')->nullable();
            $table->dateTime('data_inscricao');
            $table->foreignId('pesquisa_id')->nullable();
            $table->dateTime('resposta_pesquisa')->nullable();
            $table->dateTime('certificado_emitido')->nullable();
            $table->string('certificado_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curso_inscritos');
    }
};
