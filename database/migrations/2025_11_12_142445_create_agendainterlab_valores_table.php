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
        Schema::create('agendainterlab_valores', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('agenda_interlab_id')->constrained('agenda_interlabs')->onDelete('cascade');
            $table->string('descricao');
            $table->decimal('valor', 10, 2);
            $table->decimal('valor_assoc', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agendainterlab_valores');
    }
};