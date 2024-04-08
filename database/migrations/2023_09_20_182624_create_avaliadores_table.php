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
        Schema::create('avaliadores', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->default(new Expression("(replace(left(uuid(),12),_utf8mb3'-',_utf8mb4'0'))"));
            $table->foreignId('pessoa_id')->constrained()->cascadeOnDelete();
            $table->string('curriculo')->nullable();
            $table->boolean('exp_min_comprovada')->default(false);
            $table->boolean('curso_incerteza')->default(false);
            $table->boolean('curso_iso')->default(false);
            $table->boolean('curso_aud_interna')->default(false);
            $table->boolean('parecer_psicologico')->default(false);
            $table->date('data_ingresso')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avaliadores');
    }
};
