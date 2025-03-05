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
        Schema::create('qualificacoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('avaliador_id');
            $table->string('uid')->unique();
            $table->string('ano')->nullable();
            $table->string('instrutor')->nullable();
            $table->string('atividade')->nullable();
            $table->timestamps();

            $table->foreign('avaliador_id')->references('id')->on('avaliadores')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qualificacoes');
    }
};
