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
            $table->dropColumn([
                'valor_rs',
                'valor_s_se',
                'valor_co',
                'valor_n_ne',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agenda_interlabs', function (Blueprint $table) {
            $table->decimal('valor_rs')->nullable();
            $table->decimal('valor_s_se')->nullable();
            $table->decimal('valor_co')->nullable();
            $table->decimal('valor_n_ne')->nullable();
        });
    }
};
