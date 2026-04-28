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
            $table->text('descricao_arquivo_envio_amostras')->nullable()->after('arquivo_envio_amostras');
            $table->text('descricao_arquivo_limite_envio_resultados')->nullable()->after('arquivo_limite_envio_resultados');
            $table->text('descricao_arquivo_inicio_ensaios')->nullable()->after('arquivo_inicio_ensaios');
            $table->text('descricao_arquivo_divulgacao_relatorios')->nullable()->after('arquivo_divulgacao_relatorios');

            $table->dateTime('envio_amostras_notification')->nullable()->after('valor');
            $table->dateTime('inicio_ensaios_notification')->nullable()->after('envio_amostras_notification');
            $table->dateTime('limite_envio_resultados_notification')->nullable()->after('inicio_ensaios_notification');
            $table->dateTime('divulgacao_relatorios_notification')->nullable()->after('limite_envio_resultados_notification');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interlab_rodadas', function (Blueprint $table) {
            $table->dropColumn([
                'descricao_arquivo_envio',
                'descricao_arquivo_inicio_ensaios',
                'descricao_arquivo_limite_envio_resultados',
                'descricao_arquivo_divulgacao_relatorios',
            ]);
        });
    }
};
