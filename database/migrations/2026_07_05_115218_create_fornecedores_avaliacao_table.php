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
        Schema::create('fornecedores_avaliacao', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('agenda_interlab_id')->constrained('agenda_interlabs')->cascadeOnDelete();
            $table->foreignId('fornecedor_id')->constrained('fornecedores')->cascadeOnDelete();
            $table->unsignedTinyInteger('custo')->nullable();
            $table->unsignedTinyInteger('tempo')->nullable();
            $table->unsignedTinyInteger('qualidade')->nullable();
            $table->decimal('media', 4, 2)->nullable();
            $table->timestamps();

            $table->unique(['agenda_interlab_id', 'fornecedor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fornecedores_avaliacao');
    }
};
