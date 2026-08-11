<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agenda_avaliacoes', function (Blueprint $table) {
            $table->dropColumn([
                'soma_avaliadores',
                'soma_despesas_estimadas',
                'soma_despesas_reais',
                'num_ensaios',
                'num_aval_treinamento',
                'nf',
                'valor_proposta',
                'superavit',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('agenda_avaliacoes', function (Blueprint $table) {
            $table->integer('num_ensaios')->nullable();
            $table->decimal('soma_avaliadores')->nullable();
            $table->decimal('soma_despesas_estimadas')->nullable();
            $table->decimal('soma_despesas_reais')->nullable();
            $table->decimal('nf')->nullable();
            $table->decimal('superavit')->nullable();
            $table->decimal('valor_proposta')->nullable();
            $table->integer('num_aval_treinamento')->nullable();
        });
    }
};
