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
        Schema::create('downloads', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->string('titulo')->nullable();
            $table->string('descricao')->nullable();
            $table->enum('categoria', ['CURSOS', 'QUALIDADE', 'INTERLAB', 'INSTITUCIONAL'])->nullable();
            $table->boolean('site')->default(false);
            $table->string('arquivo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('downloads');
    }
};
