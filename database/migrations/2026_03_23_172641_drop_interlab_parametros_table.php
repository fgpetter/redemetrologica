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
        if (Schema::hasTable('interlab_parametros')) {
            DB::table('interlab_parametros')->delete();

            Schema::table('interlab_parametros', function (Blueprint $table) {
                $table->dropForeign(['agenda_interlab_id']);
                $table->dropForeign(['parametro_id']);
            });
        }

        Schema::dropIfExists('interlab_parametros');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('interlab_parametros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agenda_interlab_id')->constrained('agenda_interlabs');
            $table->foreignId('parametro_id')->constrained('parametros');
            $table->timestamps();
        });
    }
};
