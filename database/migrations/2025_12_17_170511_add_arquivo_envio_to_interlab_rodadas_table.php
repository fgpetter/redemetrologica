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
            $table->string('arquivo_envio')->nullable()->after('data_envio_amostras');
            $table->string('arquivo_inicio_ensaios')->nullable()->after('data_inicio_ensaios');
            $table->string('arquivo_limite_envio_resultados')->nullable()->after('data_limite_envio_resultados');
            $table->string('arquivo_divulgacao_relatorios')->nullable()->after('data_divulgacao_relatorios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interlab_rodadas', function (Blueprint $table) {
            $table->dropColumn('arquivo_envio');
            $table->dropColumn('arquivo_inicio_ensaios');
            $table->dropColumn('arquivo_limite_envio_resultados');
            $table->dropColumn('arquivo_divulgacao_relatorios');
        });
    }
};
