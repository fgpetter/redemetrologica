<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interlab_despesa_lancamentos', function (Blueprint $table) {
            $table->date('data_compra')->nullable();
            $table->string('tipo')->nullable();
            $table->string('especificacao')->nullable();
        });
        Schema::table('interlab_despesas', function (Blueprint $table) {
            $table->string('nota_fiscal')->nullable();
        });
        Schema::table('fornecedores_avaliacao', function (Blueprint $table) {
            $table->foreignId('avaliador_user_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('fornecedores_avaliacao', fn (Blueprint $table) => $table->dropConstrainedForeignId('avaliador_user_id'));
        Schema::table('interlab_despesas', fn (Blueprint $table) => $table->dropColumn('nota_fiscal'));
        Schema::table('interlab_despesa_lancamentos', fn (Blueprint $table) => $table->dropColumn(['data_compra', 'tipo', 'especificacao']));
    }
};
