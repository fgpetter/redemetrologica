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
        Schema::table('avaliadores', function (Blueprint $table) {
            $table->foreignId('endereco_comercial_id')->nullable()->constrained('enderecos')->nullOnDelete();
            $table->foreignId('endereco_pessoal_id')->nullable()->constrained('enderecos')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('avaliadores', function (Blueprint $table) {
            $table->dropForeign(['endereco_comercial_id']);
            $table->dropColumn('endereco_comercial_id');
            $table->dropForeign(['endereco_pessoal_id']);
            $table->dropColumn('endereco_pessoal_id');
        });
    }
};
