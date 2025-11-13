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
        Schema::table('agenda_interlabs', function (Blueprint $table) {
            $table->integer('ano_referencia')->nullable();
            $table->date('data_limite_inscricao')->nullable();
            $table->date('data_limite_envio_ensaios')->nullable();
            $table->date('data_inicio_ensaios')->nullable();
            $table->date('data_limite_envio_resultados')->nullable();
            $table->date('data_divulgacao_relatorios')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agenda_interlabs', function (Blueprint $table) {
            $table->dropColumn([
                'ano_referencia',
                'data_limite_inscricao',
                'data_limite_envio_ensaios',
                'data_inicio_ensaios',
                'data_limite_envio_resultados',
                'data_divulgacao_relatorios',
            ]);
        });
    }
};
