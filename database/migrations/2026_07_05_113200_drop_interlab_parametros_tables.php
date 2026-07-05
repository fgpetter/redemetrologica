<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('interlab_rodada_parametros');
        Schema::dropIfExists('interlab_parametros');
        Schema::dropIfExists('parametros');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tabelas recriadas pelas migrations originais; dados não são restaurados.
    }
};
