<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('laboratorios_internos', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->default(new Expression("(replace(left(uuid(),12),_utf8mb3'-',_utf8mb4'0'))"));
            $table->foreignId('laboratorio_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('area_atuacao_id');
            $table->string('nome')->nullable();
            $table->string('telefone')->nullable();
            $table->string('email')->nullable();
            $table->string('cod_labinterno')->nullable();
            $table->string('responsavel_tecnico')->nullable();
            $table->boolean('reconhecido')->default(0);
            $table->boolean('sebrae')->default(0);
            $table->boolean('site')->default(0);
            $table->string('certificado')->nullable();
            $table->timestamps();
            
            $table->foreign('area_atuacao_id')->references('id')->on('areas_atuacao')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laboratorios_internos');
    }
};
