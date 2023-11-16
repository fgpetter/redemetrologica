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
        Schema::create('unidades', function (Blueprint $table) {
            $table->id();
            $table->integer('uid');
            $table->string('nome');
            $table->foreignId('pessoa_id')->constrained()->onDelete('cascade');
            $table->foreignId('endereco_id')->constrained()->onDelet('cascade');
            $table->integer('fone')->nullable();
            $table->string('nome_responsavel')->nullable();
            $table->string('email')->nullable();
            $table->string('cod_laboratorio')->nullable();
            $table->string('responsavel_tecnico')->nullable();
            $table->integer('laboratorio_associado')->nullable();
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
