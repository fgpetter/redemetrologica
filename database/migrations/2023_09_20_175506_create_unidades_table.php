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
        Schema::create('unidades', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->default(new Expression("(replace(left(uuid(),12),_utf8mb3'-',_utf8mb4'0'))"))->unique();
            $table->foreignId('pessoa_id')->constrained()->cascadeOnDelete();
            $table->foreignId('endereco_id')->constrained()->cascadeOnDelete();
            $table->string('nome');
            $table->string('telefone')->nullable();
            $table->string('nome_responsavel')->nullable();
            $table->string('email')->nullable();
            $table->string('cod_laboratorio')->nullable();
            $table->string('responsavel_tecnico')->nullable();
            $table->integer('laboratorio_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unidades');
    }
};