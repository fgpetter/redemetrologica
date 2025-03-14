<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('materiais_padroes', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->string('descricao');
            $table->enum('tipo', ['CURSOS', 'INTERLAB', 'AMBOS']);
            $table->text('observacoes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materiais_padroes');
    }
};
