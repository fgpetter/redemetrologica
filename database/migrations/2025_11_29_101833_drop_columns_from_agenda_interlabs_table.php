<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('agenda_interlabs', function (Blueprint $table) {
            $table->dropColumn([
                'data_limite_envio_ensaios', 
                'data_inicio_ensaios', 
                'data_limite_envio_resultados', 
                'data_divulgacao_relatorios'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agenda_interlabs', function (Blueprint $table) {
            $table->date('data_limite_envio_ensaios')->nullable();
            $table->date('data_inicio_ensaios')->nullable();
            $table->date('data_limite_envio_resultados')->nullable();
            $table->date('data_divulgacao_relatorios')->nullable();
        });
    }
};
