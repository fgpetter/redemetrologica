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
        Schema::create('curso_materiais', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('curso_id');
            $table->string('arquivo');
            $table->string('descricao');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curso_materiais');
    }
};
