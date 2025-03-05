<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('laboratorios', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('pessoa_id')->constrained()->cascadeOnDelete();
            $table->string('nome_laboratorio')->nullable();
            $table->string('contato')->nullable();
            $table->string('telefone')->nullable();
            $table->string('email')->nullable();
            $table->string('cod_laboratorio')->nullable();
            $table->string('responsavel_tecnico')->nullable();
            $table->boolean('laboratorio_associado')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laboratorios');
    }
};
