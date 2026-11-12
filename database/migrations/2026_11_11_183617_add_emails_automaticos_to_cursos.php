<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('curso_inscritos', function (Blueprint $table) {
            $table->dateTime('informacoes_enviadas_em')->nullable()->after('certificado_path');
            $table->uuid('informacoes_envio_id')->nullable()->after('informacoes_enviadas_em');
        });
        Schema::table('agenda_cursos', function (Blueprint $table) {
            $table->dateTime('conferencia_enviada_em')->nullable()->after('status_proposta');
            $table->dateTime('pendencias_enviadas_em')->nullable()->after('conferencia_enviada_em');
            $table->dateTime('informacoes_enviadas_em')->nullable()->after('pendencias_enviadas_em');
            $table->uuid('conferencia_envio_id')->nullable()->after('informacoes_enviadas_em');
            $table->uuid('pendencias_envio_id')->nullable()->after('conferencia_envio_id');
        });
    }

    public function down(): void
    {
        Schema::table('curso_inscritos', fn (Blueprint $table) => $table->dropColumn(['informacoes_enviadas_em', 'informacoes_envio_id']));
        Schema::table('agenda_cursos', fn (Blueprint $table) => $table->dropColumn(['conferencia_enviada_em', 'pendencias_enviadas_em', 'informacoes_enviadas_em', 'conferencia_envio_id', 'pendencias_envio_id']));
    }
};
