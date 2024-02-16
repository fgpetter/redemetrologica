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
        Schema::create('instrutor_curso_habilitado', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->unsignedBigInteger('instrutor_id');
            $table->unsignedBigInteger('curso_id');
            $table->boolean('habilitado')->default(false);
            $table->string('conhecimento')->default(false);
            $table->string('experiencia')->default(false);
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
