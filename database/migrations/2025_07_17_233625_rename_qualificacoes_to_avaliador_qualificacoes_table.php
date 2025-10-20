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
        Schema::rename('qualificacoes', 'avaliador_qualificacoes');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('avaliador_qualificacoes', 'qualificacoes');
    }
};
