<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agenda_cursos', function (Blueprint $table) {
            $table->timestamp('fr98_enviado_em')->nullable();
            $table->uuid('fr98_envio_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('agenda_cursos', function (Blueprint $table) {
            $table->dropColumn(['fr98_enviado_em', 'fr98_envio_id']);
        });
    }
};
