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
        Schema::table('curso_inscritos', function (Blueprint $table) {
            $table->string('nome')->after('agenda_curso_id');
            $table->string('email')->after('nome');
            $table->string('telefone')->nullable()->after('email');
            $table->unsignedBigInteger('lancamento_financeiro_id')->nullable()->after('telefone');    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('curso_inscritos', function (Blueprint $table) {
            $table->dropColumn(['nome', 'email', 'telefone', 'lancamento_financeiro_id']);           
        });
    }
};
