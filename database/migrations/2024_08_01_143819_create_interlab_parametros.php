<?php

use App\Models\Parametro;
use App\Models\AgendaInterlab;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;



return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('interlab_parametros', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(AgendaInterlab::class)->constrained();
            $table->foreignIdFor(Parametro::class)->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interlab_parametros');
    }
};
