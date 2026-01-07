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
        Schema::table('interlab_inscritos', function (Blueprint $table) {
            $table->string('responsavel_tecnico')->nullable()->after('informacoes_inscricao');
            $table->string('telefone')->nullable()->after('responsavel_tecnico');
            $table->string('email')->nullable()->after('telefone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interlab_inscritos', function (Blueprint $table) {
            $table->dropColumn(['responsavel_tecnico', 'telefone', 'email']);
        });
    }
};
