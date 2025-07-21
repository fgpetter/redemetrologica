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

        Schema::table('avaliacao_avaliadores', function (Blueprint $table) {
            $table->string('inserido_por')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('avaliacao_avaliadores', function (Blueprint $table) {
            $table->dropColumn('inserido_por');
        });
    }
};

