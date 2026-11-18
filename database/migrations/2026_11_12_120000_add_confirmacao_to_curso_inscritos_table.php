<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('curso_inscritos', function (Blueprint $table) {
            $table->timestamp('confirmacao_agendada_em')->nullable()->after('informacoes_envio_id');
            $table->timestamp('confirmacao_enviada_em')->nullable()->after('confirmacao_agendada_em');
        });
    }

    public function down(): void
    {
        Schema::table('curso_inscritos', function (Blueprint $table) {
            $table->dropColumn(['confirmacao_agendada_em', 'confirmacao_enviada_em']);
        });
    }
};
