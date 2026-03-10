<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // antes de rodar a migration, limpar a tabela interlab_despesas
        DB::table('interlab_despesas')->truncate();

        // rodar a migration
        Schema::table('interlab_despesas', function (Blueprint $table) {
            $table->string('material_servico')->nullable()->after('material_padrao_id');
            $table->foreignId('fornecedor_id')->nullable()->constrained('fornecedores')->nullOnDelete()->after('material_padrao_id');
            $table->dropColumn('fornecedor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interlab_despesas', function (Blueprint $table) {
            $table->dropColumn('material_servico');
            $table->dropForeign(['fornecedor_id']);
            $table->dropColumn('fornecedor_id');
            $table->string('fornecedor')->nullable()->after('material_padrao_id');
        });
    }
};
