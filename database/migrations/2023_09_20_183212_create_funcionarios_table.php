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
        Schema::create('funcionarios', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->foreignId('pessoa_id')->constrained()->cascadeOnDelete();
            $table->string('cargo')->nullable();
            $table->string('setor')->nullable();
            $table->text('observacoes')->nullable();
            $table->date('admissao');
            $table->date('demissao')->nullable();
            $table->string('curriculo')->nullable();
            $table->integer('conta_padrao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funcionarios');
    }
};
