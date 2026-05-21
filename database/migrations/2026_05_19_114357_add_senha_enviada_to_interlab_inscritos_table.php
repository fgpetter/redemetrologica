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
            $table->dateTime('senha_enviada')->nullable()->after('tag_senha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interlab_inscritos', function (Blueprint $table) {
            $table->dropColumn('senha_enviada');
        });
    }
};
