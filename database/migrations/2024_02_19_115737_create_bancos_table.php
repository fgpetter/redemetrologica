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
        Schema::create('bancos', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->string('numero_banco')->nullable();
            $table->string('nome_banco')->nullable();
            $table->string('agencia')->nullable();
            $table->string('conta')->nullable();
            $table->boolean('movimenta_financeiro')->default(false);
            $table->boolean('padrao')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bancos');
    }
};
