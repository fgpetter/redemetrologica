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
        Schema::table('interlab_inscritos', function (Blueprint $table) {
            $table->foreignId('lancamento_financeiro_id')->nullable()->constrained('lancamentos_financeiros')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interlab_inscritos', function (Blueprint $table) {
            $table->dropForeign(['lancamento_financeiro_id']);
            $table->dropColumn('lancamento_financeiro_id');
        });
    }
};
