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
        Schema::table('agendainterlab_valores', function (Blueprint $table) {
            $table->integer('analistas')->nullable()->after('valor_assoc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agendainterlab_valores', function (Blueprint $table) {
            $table->dropColumn('analistas');
        });
    }
};