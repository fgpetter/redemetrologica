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
        Schema::table('agenda_interlabs', function (Blueprint $table) {
            $table->string('protocolo')->nullable()->after('instrucoes_inscricao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agenda_interlabs', function (Blueprint $table) {
            $table->dropColumn('protocolo');
        });
    }
};
