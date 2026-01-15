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
        Schema::table('interlab_laboratorios', function (Blueprint $table) {
            $table->dropColumn(['responsavel_tecnico', 'telefone', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interlab_laboratorios', function (Blueprint $table) {
            $table->string('responsavel_tecnico')->nullable();
            $table->string('telefone')->nullable();
            $table->string('email')->nullable();
        });
    }
};
