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
        Schema::create('certificados_avaliador', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->unsignedBigInteger('avaliador_id');
            $table->date('data')->nullable();
            $table->string('revisao')->nullable();
            $table->string('responsavel')->nullable();
            $table->string('motivo')->nullable();
            $table->timestamps();

            $table->foreign('avaliador_id')->references('id')->on('avaliadores')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificados_avaliador');
    }
};
