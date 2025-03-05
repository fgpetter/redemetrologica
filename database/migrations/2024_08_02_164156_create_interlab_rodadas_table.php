<?php

use App\Models\Parametro;
use App\Models\AgendaInterlab;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('interlab_rodadas', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignIdFor(AgendaInterlab::class)->constrained()->onDelete('cascade');
            $table->string('descricao');
            $table->integer('vias');
            $table->text('cronograma')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interlab_rodadas');
    }
};
