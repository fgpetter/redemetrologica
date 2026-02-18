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
        Schema::create('interlab_analistas', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('interlab_inscrito_id')->constrained('interlab_inscritos')->onDelete('cascade');
            $table->string('tag_senha')->nullable();
            $table->string('certificado_path')->nullable();
            $table->string('nome');
            $table->string('email');
            $table->string('telefone');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interlab_analistas');
    }
};
