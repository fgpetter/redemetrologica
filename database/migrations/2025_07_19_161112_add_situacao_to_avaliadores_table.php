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
        // GEMINI: Alteração feita para adicionar a coluna situacao na tabela avaliadores
        Schema::table('avaliadores', function (Blueprint $table) {
            $table->enum('situacao', ['ATIVO', 'AVALIADOR', 'AVALIADOR EM TREINAMENTO', 'AVALIADOR LIDER', 'ESPECIALISTA', 'INATIVO'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('avaliadores', function (Blueprint $table) {
            $table->dropColumn('situacao');
        });
    }
};
