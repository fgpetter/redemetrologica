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
        Schema::table('lancamentos_financeiros', function (Blueprint $table) {
            $table->enum('forma_pagamento', ['boleto_bancario', 'deposito_bancario'])->nullable()->after('observacoes');
            $table->boolean('exige_pedido_compra')->nullable()->after('forma_pagamento');
            $table->boolean('entidade_governamental')->nullable()->after('exige_pedido_compra');
            $table->enum('esfera_governamental', ['federal', 'estadual', 'municipal'])->nullable()->after('entidade_governamental');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lancamentos_financeiros', function (Blueprint $table) {
            $table->dropColumn(['forma_pagamento', 'exige_pedido_compra', 'entidade_governamental', 'esfera_governamental']);
        });
    }
};
