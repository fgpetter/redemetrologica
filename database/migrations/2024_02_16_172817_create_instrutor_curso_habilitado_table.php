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
        Schema::create('instrutor_curso_habilitado', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('instrutor_id')->constrained('instrutores')->cascadeOnDelete();
            $table->foreignId('curso_id')->constrained()->cascadeOnDelete();
            $table->boolean('habilitado')->default(false);
            $table->boolean('conhecimento')->default(false);
            $table->boolean('experiencia')->default(false);
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instrutor_curso_habilitado');
    }
};
