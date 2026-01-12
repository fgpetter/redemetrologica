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
        Schema::table('interlab_rodadas', function (Blueprint $table) {
            $table->dropColumn(['cronograma']);
            $table->date('data_envio_amostras')->nullable()->after('vias');
            $table->date('data_inicio_ensaios')->nullable()->after('data_envio_amostras');
            $table->date('data_limite_envio_resultados')->nullable()->after('data_inicio_ensaios');
            $table->date('data_divulgacao_relatorios')->nullable()->after('data_limite_envio_resultados');
            $table->decimal('valor', 10, 2)->nullable()->after('data_divulgacao_relatorios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interlab_rodadas', function (Blueprint $table) {
            $table->string('cronograma')->nullable()->after('vias');
            $table->dropColumn([
                'data_envio_amostras', 
                'data_inicio_ensaios', 
                'data_limite_envio_resultados', 
                'data_divulgacao_relatorios'
            ]);
        });
    }
};
